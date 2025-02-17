-- Drop the existing foreign key constraint
ALTER TABLE article DROP FOREIGN KEY FK_23A0E66FD02F13;

-- Add a new foreign key constraint with ON DELETE CASCADE and ON UPDATE CASCADE
ALTER TABLE article
ADD CONSTRAINT FK_23A0E66FD02F13
FOREIGN KEY (evenement_id)
REFERENCES evenement(id)
ON DELETE CASCADE
ON UPDATE CASCADE;
