import ConfigParser, os, sys
from binascii import hexlify, unhexlify
from simplecrypt import encrypt, decrypt

salt_type = sys.argv[1]
print salt_type
item = sys.argv[2]
print item

config = ConfigParser.ConfigParser()
config.readfp(open('../config/.salt'))
password = config.get('Dolphin', salt_type)

decrypted = decrypt(password, unhexlify(item)).decode('utf8')
print decrypted
