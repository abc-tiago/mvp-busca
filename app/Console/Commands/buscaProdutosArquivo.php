<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Libraries\DateUtils;
use App\Http\Controllers\Api\ProdutosController;

class buscaProdutosArquivo extends Command
{

    protected $signature = 'command:buscaProdutosArquivo';
    protected $description = 'Command para fazer consultar e colocar no cache produtos para fazer o auto complete da busca';

    public function handle(): void
    {
        $horaInicio = Carbon::now();
        $this->info("INICIO - " . $horaInicio->format('d/m/Y - H:i:s'));

        ProdutosController::rotinaBuscaProdutoSalvarArquivo();

        // MovimentacoesController::buscarInformacoes($date ?? '');

        $this->info("FINAL - " . Carbon::now()->format('d/m/Y - H:i:s'));
        $this->info("DONE - " . DateUtils::diffInterval($horaInicio));
    }
}
