<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
	private $extension;

	// récupérer l'extension
	public function getExtension(UploadedFile $uploadedFile):string
	{
		$this->extension = $uploadedFile->guessExtension();
		return $this->extension;
	}

	// transférer un fichier
	public function upload(UploadedFile $uploadedFile, string $destination, string $fileName):void
	{
		$uploadedFile->move($destination, $fileName);
	}

	// supprimer un fichier
	public function delete(string $directory, string $fileName):void
	{
		unlink("$directory/$fileName");
	}
}









