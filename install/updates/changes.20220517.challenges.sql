CREATE TABLE 
  s_yf_challenges
  (
    module_name VARCHAR(25) NOT NULL,
    record_id INT NOT NULL,
    challenge_code VARCHAR(255) NOT NULL,
    challenge_date DATETIME NOT NULL,
    KEY idx_challanges_code ( challenge_code, challenge_date )
  );
