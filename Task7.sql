CREATE TABLE IF NOT EXISTS 	User_Token(
	tokenId SERIAL NOT NULL PRIMARY KEY,
	userId INT,
	token varchar(255) NOT NULL,
	created_at timestamp(0) without time zone,
	updated_at timestamp(0) without time zone,
	CONSTRAINT fk_user 
	FOREIGN KEY (userid)
        		REFERENCES users (userid)
)

