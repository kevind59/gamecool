<?php

namespace App\Service;

class StringService
{
	// générer un token (suite de caractères aléatoires)
	public function generateToken(int $length):string
	{
		$result = bin2hex(random_bytes($length));
		return $result;
	}
}