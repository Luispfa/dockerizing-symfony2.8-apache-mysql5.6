<?php
  	namespace Lugh\DbConnectionBundle\Service;

  	class ImageManager
  	{
  		public function resizeImage($source_path,$width,$height,$mantainAspect = true){
  			
  			try{
  				$source_image  = $this->getImageObject($source_path);
				$source_imagex = imagesx($source_image);
				$source_imagey = imagesy($source_image);

				if($source_imagex < $width && $source_imagey < $height){
					return;
				}

				//$source_aspect = $source_imagex / $source_imagey;

				$dest_imagex = $width;
				$dest_imagey = $height;
				if($mantainAspect){
					$factor = min( $width / $source_imagex, $height / $source_imagey );
                    $dest_imagex  = round( $source_imagex * $factor );
                    $dest_imagey = round( $source_imagey * $factor );
				}
				$dest_image = imagecreatetruecolor($dest_imagex, $dest_imagey);
				imagealphablending($dest_image, false);
				imagesavealpha($dest_image,true);
				$transparent = imagecolorallocatealpha($dest_image, 255, 255, 255, 127);
				imagefilledrectangle($dest_image, 0, 0, $dest_imagex, $dest_imagey, $transparent);

				imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $dest_imagex, $dest_imagey, $source_imagex, $source_imagey);

				$this->saveImageObject($dest_image,$source_path);

  			}catch(\Exception $exc){
  				var_dump('error'.$exc);
                return false;
            }
  		}

  		private function getImageObject($source_path){
  			$image = null;
  			
  			$finfo = finfo_open(FILEINFO_MIME_TYPE); // devuelve el tipo mime de su extensión

            switch (finfo_file($finfo, $source_path))
            {
				case 'image/gif':
					$image = imagecreatefromgif($source_path);
					break;
				case 'image/jpg':
				case 'image/jpeg':
					$image = imagecreatefromjpeg($source_path);
					break;
				case 'image/png':
					$image = imagecreatefrompng($source_path);
					break;
				default: throw new Exception('error');
			}
			finfo_close($finfo);
           
  			return $image;	
  		}

  		private function saveImageObject($dest_image,$out_path){
  			$quality = 9;

  			//header("Content-Type: image/jpeg");
			//imagejpeg($dest_image,$out_path,10 * $quality);
				
			//header("Content-Type: image/png");
			//imagepng($dest_image,'2'.$out_path,$quality);


			header( "Content-type: image/png" );
			imagepng($dest_image);
			chmod($out_path,0755);
			imagepng($dest_image, $out_path, 0, NULL);
			imagedestroy($dest_image);
  		}


  	}