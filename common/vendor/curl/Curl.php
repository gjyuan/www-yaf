<?php
class Vendor_Curl_Curl extends Vendor_Curl_Instance {
    private $_isSington = true;
    private $_curlInstance;
    public function __construct($url="",$isSington = true){
        $this->_isSington = $isSington;
        parent::__construct($url);
    }
    protected function __curlInit(){
        if($this->_isSington && !empty($this->_curlInstance)) {
            return $this->_curlInstance;
        }
        $this->_curlInstance = parent::__curlInit();
        return $this->_curlInstance;
    }
    /**
     * @param bool $isSington
     */
    public function setIsSington(bool $isSington)
    {
        $this->_isSington = $isSington;
    }
    /**
     * @return array
     * @throws Exception
     */
    public function httpExecute(){
        try{
            $ch = $this->__curlInit();
            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno == CURLE_COULDNT_CONNECT) {
                $msg = "Curl error [Can not connect " . $this->getUrl() . " error:" . $errno . '-' . curl_error($ch) ."]";
                return [false,$msg];
            }
            if ($errno != CURLE_OK) {
                $msg = "Curl error [errorno:{$errno}-" .curl_error($ch)."]";
                return [false,$msg];
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code != 200) {
                $msg = "Curl error [code:{$code}]";
                return [false,$msg];
            }
            $data = is_string($result) ? json_decode($result, true) : $result;
            return [true,$data];
        }catch (Exception $e){
            return [false,$e->getMessage()];
        }
    }

}