<?php

use Alura\Fp\Maybe;

use function igorw\pipeline;

require_once 'vendor/autoload.php';

/** @var Maybe $dados */
$dados = require 'dados.php';

$contador = count($dados->getOrElse([]));

echo "Número de países: $contador\n";

function convertePaisParaLetraMaiuscula (array $pais): array
{
    $pais['pais'] = mb_convert_case($pais['pais'], MB_CASE_UPPER);
    return $pais;
}

$somaMedalhas = fn (int $medalhasAcumuladas, int $medalhas): int => $medalhasAcumuladas + $medalhas;

$medalhasAcumuladas = fn (int $medalhasAcumuladas, array $pais): int 
    => $medalhasAcumuladas + array_reduce($pais['medalhas'], $somaMedalhas, 0);

$verificaSePaisTemEspacoNoNome = fn (array $pais): bool => str_contains($pais['pais'], ' ');

$comparaMedalhas = fn (array $medalhasPais1, array $medalhasPais2) 
    => fn (string $modalidade): int => $medalhasPais2[$modalidade] <=> $medalhasPais1[$modalidade];

$nomesDePaisesEmMaiusculo = fn (Maybe $dados) => Maybe::of(array_map('convertePaisParaLetraMaiuscula', $dados->getOrElse([])));
$filtraPaisesSemEspacoNoNome = fn (Maybe $dados) => Maybe::of(array_filter($dados->getOrElse([]), $verificaSePaisTemEspacoNoNome));

$funcoes = pipeline(
    $nomesDePaisesEmMaiusculo, 
    $filtraPaisesSemEspacoNoNome
);
$dados = $funcoes($dados);

var_dump($dados->getOrElse([]));

exit();


$medalhas = array_reduce(
    array_map(
        fn (array $medalhas): int => array_reduce($medalhas, $somaMedalhas, 0), 
        array_column($dados, 'medalhas')
    ), $somaMedalhas, 0
);

usort($dados, function (array $pais1, array $pais2) use ($comparaMedalhas) {
    $medalhasPais1 = $pais1['medalhas'];
    $medalhasPais2 = $pais2['medalhas'];

    $comparador = $comparaMedalhas($medalhasPais1, $medalhasPais2);

    return $comparador('ouro') !== 0 ? $comparador('ouro') 
        : ($comparador('prata') !== 0 ? $comparador('prata') 
        : $comparador('bronze')
    );
});

var_dump($dados);

echo $medalhas;