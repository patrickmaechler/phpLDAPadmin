<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

function dn_container($string): string
{
	return collect(explode(',',$string))->skip(1)->join(',');
}

function dn_rdn($string): string
{
	return collect(explode(',',$string))->first();
}

function can_create_children_in_tree(?string $dn): bool
{
	if (! is_string($dn) || $dn === '')
		return TRUE;

	$rdn = dn_rdn($dn);
	if ($rdn === '')
		return TRUE;

	foreach (explode('+',$rdn) as $part) {
		$attribute = strtolower(trim((string) explode('=',$part,2)[0]));
		$attribute = preg_replace('/;.*$/','',$attribute) ?? $attribute;

		if (in_array($attribute,['cn','uid'],TRUE))
			return FALSE;
	}

	return TRUE;
}

function login_attr_description(): string
{
	return Arr::get(config('pla.login.attr'),login_attr_name());
}

function login_attr_name(): string
{
	return key(config('pla.login.attr'));
}

/**
 * Used to generate a random salt for crypt-style passwords. Salt strings are used
 * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses
 * not only the user's password but also a randomly generated string. The string is
 * stored as the first N characters of the hash for reference of hashing algorithms later.
 *
 * @param int $length The length of the salt string to generate.
 * @return string The generated salt string.
 * @throws \Random\RandomException
 */
function random_salt(int $length): string
{
	$str = bin2hex(random_bytes(ceil($length/2)));
	if ($length%2 === 1)
		return substr($str,0,-1);

	return $str;
}

/**
 * For any incoming request, work out the command and DN involved
 *
 * @param string|null $string $string
 * @return array
 */
function request_key(?string $string): array
{
	if (is_null($string))
		return [];

	$cmd = NULL;
	$dn = NULL;
	$key = Crypt::decryptString($string);

	// Determine if our key has a command
	if (str_contains($key,'|')) {
		$m = [];

		if (preg_match('/\*([a-z_]+)\|(.+)$/',$key,$m)) {
			$cmd = $m[1];
			$dn = ($m[2] !== '_NOP') ? $m[2] : NULL;
		}

	} else {
		$cmd = 'dn';
		$dn = $key;
	}

	return ['cmd'=>$cmd,'dn'=>$dn];
}