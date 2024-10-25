import hashlib

# MySQL 160 bit - Multiple SHA-1 Encryption
def mysql_password_hash(password):
    # 1차 해싱 # 평문을 바이트 형식으로 변환 후, SHA 1 해싱 후, 16진수로 변환
    hash1 = hashlib.sha1(password.encode()).hexdigest()
    
    # 2차
    hash2 = hashlib.sha1(bytes.fromhex(hash1)).hexdigest()
    # MySQL 형식으로 변환
    return "*" + hash2.upper()

password = "hacked"
print(mysql_password_hash(password)) 