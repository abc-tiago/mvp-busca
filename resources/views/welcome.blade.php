<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Buscador</title>

        <link rel="stylesheet" href="./css/index.css" />

        <script src="https://unpkg.com/phosphor-icons"></script>
        <!-- <script type="text/javascript" src="./index.js" defer=""></script> -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script type="text/javascript" src="./js/search.js" defer=""></script>

        <script src="https://cdn.jsdelivr.net/npm/minisearch@6.1.0/dist/umd/index.min.js"></script>

    </head>
    <body>

        <header>
            <h1>Como posso ajudá-lo?</h1>
            <div class="input-wrapper">
                <label for="filter" class="sr-only">BUSCAR</label>
                <input
                id="input_search"
                class="input_search"
                type="text"
                placeholder="O que você procura?"
                />
                <i class="ph-magnifying-glass"></i>
                <i class='load'></i>
            </div>

            <div class="input-suggestion">
                <div class="modalBusca">
                    <div class="maisbuscados">
                        <main>
                            <h5>mais buscados</h5>
                            <ul class="cards"></ul>
                        </main>
                    </div>

                    <div class="autocomplete">
                        <main></main>
                    </div>
                </div>
            </div>
        </header>

        <main></main>
  </body>
</html>
