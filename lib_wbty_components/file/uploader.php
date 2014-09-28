<?php
/**
 * @version     {version}
 * @package     {com_name}
 * @copyright   {copyright}
 * @license     {license}
 * @author      {author}
 */

defined('_JEXEC') or die;

/*
* Example implementation
*
* // list of valid extensions, ex. array("jpeg", "xml", "bmp")
* $allowedExtensions = array('jpg', 'jpeg', 'png');
* // max file size in bytes
* $sizeLimit = 10 * 1024 * 1024;
*		
* $uploader = new WbtyFileUploader($allowedExtensions, $sizeLimit);
*
* $result = $uploader->handleUpload($directory);
*
* returns array
*
*/

class WbtyFileUploader
{
	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
	public function getName(){
		if ($this->file)
			return $this->file->getName();
	}
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = @$pathinfo['extension'];		// hide notices if extension is empty

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true, 'filename'=>$filename, 'ext'=>$ext);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }

    public static function generateThumbnail($file, $id, $thumbfolder = 'media/wbty_components/thumbs/', $maxsides = array('small'=>150, 'medium'=>400, 'large'=>1000)) {
		if ( !($image=self::openImage(JPATH_ROOT . '/' . $file)) || !$id) {
			// TODO: Add Fail handler
			return false;
		}

        if (!JFolder::exists(JPATH_ROOT.'/'.$thumbfolder)) {
            JFolder::create(JPATH_ROOT.'/'.$thumbfolder);
        }
		
		$basename = basename($file);
		$width  = imagesx($image);  
        $height = imagesy($image);
		$ext = strtolower(strrchr($file, '.')); 
		
		foreach ($maxsides as $key=>$maxside) {
			$newwidth = $newheight = 0;
			
			if ($width >= $height) {
				$newwidth = $maxside;
				$newheight = $newwidth*$height/$width;
			} elseif ($height > $width) {
				$newheight = $maxside;
				$newwidth = $newheight*$width/$height;
			}
			
			if ($newheight && $newwidth) {
				$newimage = imagecreatetruecolor($newwidth, $newheight);  
				
				$extension = strtolower(strrchr($file, '.'));
				switch($extension)
				{
					case '.gif': 
						$background = imagecolorallocate($newimage, 0, 0, 0);
						imagecolortransparent($newimage, $background);
						break;  
					case '.png':  
						$background = imagecolorallocate($newimage, 0, 0, 0);
						imagecolortransparent($newimage, $background);
						imagealphablending($newimage, false);
						imagesavealpha($newimage, true);
						break;  
				}  
				
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				
				if (!self::saveImage($newimage, $thumbfolder . $id . '-' . $key . $ext)) {
					// TODO: Add fail handler
				}
			}
		}
		
		return true;
	}
	
	private function openImage($file)  
	{  
		// *** Get extension  
		$extension = strtolower(strrchr($file, '.'));
		switch($extension)
		{  
			case '.jpg':  
			case '.jpeg':  
				$img = @imagecreatefromjpeg($file);  
				break;  
			case '.gif':  
				$img = @imagecreatefromgif($file);  
				break;  
			case '.png':  
				$img = @imagecreatefrompng($file);  
				$background = imagecolorallocate($img, 0, 0, 0);
				imagecolortransparent($img, $background);
				imagealphablending($img, false);
				imagesavealpha($img, true);
				break;  
			default:
				$img = false;  
				break;  
		}  
		return $img;  
	} 
	
	public function saveImage($image, $savePath, $imageQuality="100")  
	{  
		// *** Get extension  
			$extension = strrchr($savePath, '.');  
			$extension = strtolower($extension);  
		switch($extension)  
		{  
			case '.jpg':  
			case '.jpeg':  
				if (imagetypes() & IMG_JPG) {  
					imagejpeg($image, JPATH_ROOT . '/' . $savePath, $imageQuality);  
				}  
				break;  
			case '.gif':  
				if (imagetypes() & IMG_GIF) {  
					imagegif($image, JPATH_ROOT . '/' . $savePath);  
				}  
				break;  
			case '.png':  
				// *** Scale quality from 0-100 to 0-9  
				$scaleQuality = round(($imageQuality/100) * 9);  
				// *** Invert quality setting as 0 is best, not 9  
				$invertScaleQuality = 9 - $scaleQuality;  
				if (imagetypes() & IMG_PNG) {  
					imagepng($image, JPATH_ROOT . '/' . $savePath, $invertScaleQuality);  
				}  
				break;  
			// ... etc  
			default:  
				// *** No extension - No save.  
				break;  
		}  
		imagedestroy($image); 
		return $savePath; 
	}
}

// helper classes
/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}