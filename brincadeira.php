<?php
$variavel = 'teste';

function outra(callable $funcao): void
{
    echo 'Executando outra função: ';
    echo $funcao();
}

$nomeDaFuncao = function () use ($variavel) {
    echo $variavel;
    return 'Uma outra função:';
};

outra($nomeDaFuncao);