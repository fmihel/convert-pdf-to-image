<?php
namespace fmihel\ConvertPdfToImage;


class ConvertPdfToImage{
    
    protected $filename = ''; // original (from user) name of pdf file
    protected $realFileName = ''; // real name of pdf file (must be not equal to original)
    
    protected $imagick; 
    protected $count; //count of pages (reloaded on after call Load)
    protected $tmpPath = __DIR__.'/';
    protected $needDeleteTmpFile = false; // if created tmp files this prop set true
    
    protected $params = [
        'resolution'    =>144,      // resolution
        'page'          =>'all',    // page number or 'all'
        'format'        =>'jpg',    // jpg | png
        'addIndexPageToName'=>false,// only for page === number
        'needHeader'=>false,        // out header("Content-Type: image/...) before out to browwser (for out only)
        'outReturn' =>false,        // out return image as result or not to browser (for out only)
    ];
    
    
    function __construct($filename = '',$params=[]){
        $this->params = array_merge($this->params,$params);
        if ($filename!=='')
            $this->load($filename);
            
        return $this;    
    }
    
    function __destruct(){
        $this->_clear();
    }
    /**
     * устанавливает или возвращает параметры настройки 
     * Установка:
     * param('resolution',300);
     * param(['resolution'=>300,...]);
     * Возврат:
     * $res = param('resolution');
     */ 
    public function param($name/*value*/){
        
        if (gettype($name) === 'array'){
            $this->params = array_merge($this->params,$name);
            return;
        }
        
        if (func_num_args()>1){
            $value = func_get_arg(1);
            $this->params = array_merge($this->params,[$name=>$value]);
        };
        
        return $this->params[$name];
    }
    /**
     * загрузка файла, можно использовать удаленный путь
     */ 
    public function load($filename){
        try{
            $this->_clear();
            
            if ($this->_isRemote($filename)){
                $this->realFileName = $this->tmpPath.'_'.$this->random_str(7).'.pdf';
                $this->_loadFromUrl($filename,$this->realFileName);
                $this->needDeleteTmpFile = true;
            } else {
                $this->realFileName = $filename;
            };
            
            $this->realFileName  = realpath($this->realFileName);
            $this->filename = $filename;
            
            $this->imagick = new \Imagick();
            $this->imagick->readImage($this->realFileName);
            $this->count = $this->imagick->getNumberImages();
            
            return $this;
            
        }catch(\Exception $e){
            error_log(__METHOD__.' Exception: '.$e->getMessage());
        }
        
    
    }
    private function _read(&$params){
        
        $param = array_merge($this->params,$params);

        if (!file_exists($this->realFileName))
            throw new \Exception("file [$this->realFileName] not exists or empty(use load)");
                
        $this->imagick = new \Imagick();
        $page = $param['page'] !== 'all' ?'['.$param['page'].']' : '' ;
        $this->imagick->readImage($this->realFileName.$page);
        
        $this->imagick->setResolution($param['resolution'],$param['resolution']);
        $this->imagick->setImageFormat($param['format']);            

    }    
    /** 
    * сохранение файла на диск 
    */
    public function save($toFileName,$params=[]){
        
        try{

            $this->_read($params);
            if ($params['page'] === 'all'){
                for($i = 0 ;$i<$this->count;$i++)
                    $this->save($toFileName,['addIndexPageToName'=>true,'page'=>$i]);
                
            }else{
                
                if ($params['addIndexPageToName'])
                    $toFileName = $this->addIndex($toFileName,$params['page']);
                $this->imagick->writeImage($toFileName);
            }
            
            return $this;
            
        }catch(\Exception $e){
            error_log(__METHOD__.' Exception: '.$e->getMessage());
        }
        
    }
    /**
     * вывод файла
     */ 
    public function out($params=[]){
        try{
            
            $this->_read($params);

            $tmpFile = $this->tmpPath.$this->random_str(10).'jpg';
            $this->imagick->writeImage($tmpFile);
            $out = file_get_contents($tmpFile);
            unlink($tmpFile);
            
            if (!$params['outReturn']){
                
                if ($params['needHeader'])
                    header("Content-Type: image/".$params['format']);
                
                echo $out;
                return $this;
            }
            
            return $out;

        }catch(\Exception $e){
            error_log(__METHOD__.' Exception: '.$e->getMessage());
        }
    }
    
    public function count(){
        return $this->count;
    }
    /**
    * Признак, что маршрут удаленный
    */
    private function _isRemote($filename){
        return  ( strpos($filename,'http://')!==false ) || ( strpos($filename,'https://')!==false );
    }
    
    private function _loadFromUrl($url,$saveAs){
        $data = file_get_contents($url);
        if ($data === false)
            throw new \Exception(__METHOD__.": can`t load [$url]");
        if (!file_put_contents($saveAs,$data))
            throw new \Exception(__METHOD__.": can`t save [$url] to [$saveAs]");
    }
    
    private function random_str($count = 5){
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomString = ''; 
  
        for ($i = 0; $i < $count; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 
  
        return $randomString; 
    } 
    
    private function addIndex($filename,$index){
        $info = pathinfo($filename);
        $file = $info['basename'];
        $pos = strrpos($file,'.');
        
        if ($pos!==false)
            $file=substr($file,0,$pos).'_'.$index.'.'.$info['extension']; 
        else
            $file.='_'.$index;
            
        return $info['dirname'].($info['dirname']!==''?'/':'').$file;
    }
    /**
     * сборка мусора
    */
    private function _clear(){
        if ($this->needDeleteTmpFile)
            unlink($this->realFileName);    
        $this->needDeleteTmpFile = false;            
        $this->count = 0;
    }
};

?>