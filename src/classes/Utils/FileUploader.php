<?php
namespace AILifestyle\Utils;

class FileUploader
{
  private $uploadDir;
  private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  private $maxFileSize = 5242880; // 5MB

  public function __construct( $uploadDir = null )
  {
    $this->uploadDir = $uploadDir ?? dirname( __DIR__, 2 ) . '/uploads/';
    
    // Create upload directory if it doesn't exist
    if( ! is_dir( $this->uploadDir ) )
    {
      mkdir( $this->uploadDir, 0755, true );
    }
  }

  public function uploadImage( $file ) : array
  {
    // Validate file
    if( ! isset( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) )
    {
      throw new \Exception( "No file was uploaded" );
    }

    // Check file size
    if( $file['size'] > $this->maxFileSize )
    {
      throw new \Exception( "File is too large. Maximum size is " . ($this->maxFileSize / 1024 / 1024) . "MB" );
    }

    // Check file type
    $finfo = new \finfo( FILEINFO_MIME_TYPE );
    $fileType = $finfo->file( $file['tmp_name'] );
    
    if( ! in_array( $fileType, $this->allowedImageTypes ) )
    {
      throw new \Exception( "Invalid file type. Allowed types: " . implode( ', ', $this->allowedImageTypes ) );
    }

    // Generate unique ID for the file
    $uniqueId = uniqid( '', true );
    $extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
    $newFilename = $uniqueId . '.' . $extension;
    $destination = $this->uploadDir . $newFilename;

    // Move the file
    if( ! move_uploaded_file( $file['tmp_name'], $destination ) )
    {
      throw new \Exception( "Failed to save the uploaded file" );
    }

    return [
      'id' => $uniqueId,
      'filename' => $newFilename,
      'original_name' => $file['name'],
      'path' => $destination,
      'type' => $fileType
    ];
  }

  public function getFilePath( $filename ) : string
  {
    return $this->uploadDir . $filename;
  }

  public function deleteFile( $filename ) : bool
  {
    $filePath = $this->getFilePath( $filename );
    
    if( file_exists( $filePath ) )
    {
      return unlink( $filePath );
    }
    
    return false;
  }
}
