<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/panier')]
final class PanierController extends AbstractController
{
    #[Route(name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($panier);
            $entityManager->flush();

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('panier/new.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $panier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/validate', name: 'app_panier_validate', methods: ['POST'])]
    public function validatePanier(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('validate' . $panier->getId(), $request->request->get('_token'))) {
            // Clear all products from the panier
            foreach ($panier->getProduits() as $produit) {
                $panier->removeProduit($produit);
            }

            // Update the MajLe timestamp
            $panier->setMajLe(new \DateTimeImmutable());

            $entityManager->persist($panier);
            $entityManager->flush();

            $this->addFlash('success', 'Panier validated and cleared successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_panier_index');
    }


    #[Route('/checkout/cart/products', name: 'app_panier_checkout')]
    public function checkout(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $entityManager, SessionInterface $session): JsonResponse
    {
        // Decode the cart JSON from the request
        $cart = json_decode($request->getContent(), true);
        if (!$cart || empty($cart)) {
            return new JsonResponse(['error' => 'Panier vide'], Response::HTTP_BAD_REQUEST);
        }

        // Store cart data in session
        $session->set('cart_data', $cart);

        // Initialize Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $lineItems = [];
        foreach ($cart as $item) {
            $produit = $produitRepository->find($item['id']);
            if (!$produit) {
                return new JsonResponse(['error' => "Produit ID {$item['id']} non trouvé"], Response::HTTP_BAD_REQUEST);
            }

            // Update the ventes count
            $produit->setVentes($produit->getVentes() + $item['quantity']);
            $entityManager->persist($produit);

            // Add product to Stripe checkout session
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name' => $produit->getNom(),
                    ],
                    'unit_amount'  => $produit->getPrix() * 100, // Convert to cents
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Save changes in the database
        $entityManager->flush();

        // Create Stripe Checkout session
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['url' => $checkoutSession->url]);
    }


    #[Route('/checkout/success', name: 'app_checkout_success', methods: ['GET'])]
    public function checkoutSuccess(MailerInterface $mailer, SessionInterface $session): Response
    {
        // Retrieve cart data from session
        $cart = $session->get('cart_data', []);

        if (empty($cart)) {
            throw $this->createNotFoundException('No cart data found.');
        }

        // Generate PDF invoice
        $pdfContent = $this->generateInvoicePDF($cart);

        // Send Email with PDF attachment
        $this->sendInvoiceEmail($mailer, $cart, $pdfContent);

        // Clear session cart data after successful purchase
        $session->remove('cart_data');

        return $this->render('panier/success.html.twig');
    }
    private function generateInvoicePDF(array $cart): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = $this->renderView('panier/invoice.html.twig', ['cart' => $cart]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output(); // Ensure this is binary data, not a string
    }


    private function sendInvoiceEmail(MailerInterface $mailer, array $cart, string $pdfContent): void
    {
        // Create a temporary file
        $tempPdfPath = sys_get_temp_dir() . '/facture_' . uniqid() . '.pdf';
        file_put_contents($tempPdfPath, $pdfContent);

        // Create email
        $email = (new Email())
            ->from('no-reply@yourwebsite.com')
            ->to('medslama.194@gmail.com') // Change to actual customer email
            ->subject('Votre Facture - Merci pour votre achat !')
            ->html('<p>Merci pour votre achat. Vous trouverez votre facture en pièce jointe.</p>')
            ->attachFromPath($tempPdfPath, 'facture.pdf', 'application/pdf');

        // Send the email
        $mailer->send($email);

        // Remove temporary file
        unlink($tempPdfPath);
    }



    #[Route('/checkout/cancel', name: 'app_checkout_cancel', methods: ['GET'])]
    public function checkoutCancel(): Response
    {
        return $this->render('panier/cancel.html.twig');
    }
}
