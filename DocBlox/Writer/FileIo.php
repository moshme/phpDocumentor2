<?php
class DocBlox_Writer_FileIo extends DocBlox_Writer_Abstract
{
  /** @var DocBlox_Transformation */
  protected $transformation = null;

  /** @var DOMDocument */
  protected $structure = null;

  /**
   * Invokes the query method contained in this class.
   *
   * @throws InvalidArgumentException
   *
   * @param DOMDocument            $structure
   * @param DocBlox_Transformation $transformation
   *
   * @return void
   */
  public function transform(DOMDocument $structure, DocBlox_Transformation $transformation)
  {
    $artifact = $transformation->getTransformer()->getTarget() . DIRECTORY_SEPARATOR . $transformation->getArtifact();
    $transformation->setArtifact($artifact);
    $this->transformation = $transformation;

    $method   = 'query'.ucfirst($transformation->getQuery());
    if (!method_exists($this, $method))
    {
      throw new InvalidArgumentException(
        'The query ' . $method . ' is not supported by the FileIo writer'
      );
    }

    $this->$method();
  }

  /**
   * Copies files or folders to the Artifact location.
   *
   * @throws Exception
   *
   * @return void
   */
  public function queryCopy()
  {
    if (!is_readable($this->transformation->getSource()))
    {
      throw new Exception('Unable to read the source file: ' . $this->transformation->getSource());
    }

    if (!is_writable($this->transformation->getTransformer()->getTarget()))
    {
      throw new Exception('Unable to write to: ' . dirname($this->transformation->getArtifact()));
    }

    if (is_dir($this->transformation->getSource()))
    {
      $this->copyRecursive($this->transformation->getSource(), $this->transformation->getArtifact());
    }
    else
    {
      copy($this->transformation->getSource(), $this->transformation->getArtifact());
    }
  }

  /**
   * Copies a folder recursively to another location.
   *
   * @throws Exception
   *
   * @param string $src
   * @param string $dst
   *
   * @return void
   */
  protected function copyRecursive($src, $dst)
  {
    $dir = opendir($src);
    if (!$dir)
    {
      throw new Exception('Unable to locate path "' . $src . '"');
    }

    // check if the folder exists, otherwise create it
    if ((!file_exists($dst)) && (false === mkdir($dst)))
    {
      throw new Exception('Unable to create folder "' . $dst . '"');
    }

    while (false !== ($file = readdir($dir)))
    {
      if (($file != '.') && ($file != '..'))
      {
        if (is_dir($src . '/' . $file))
        {
          $this->copyRecursive($src . '/' . $file, $dst . '/' . $file);
        }
        else
        {
          copy($src . '/' . $file, $dst . '/' . $file);
        }
      }
    }
    closedir($dir);
  }
}