<?php
class Crypto {
    private $encryptKey;
    
    public function __construct() {
    	$options = get_option('wbc_options');
    	$wbc_key = $options['wbc_merchant_key'];
		$this->encryptKey = $wbc_key;
	}
	
	public function encrypt($toEncrypt){
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->encryptKey, $toEncrypt, MCRYPT_MODE_CBC, $iv));
    }
     public function decrypt($toDecrypt){
       $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);//$this->blocksize;
       $toDecrypt = base64_decode($toDecrypt);
       return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->encryptKey, substr($toDecrypt, $iv_size), MCRYPT_MODE_CBC, substr($toDecrypt, 0, $iv_size)));
    }
}

