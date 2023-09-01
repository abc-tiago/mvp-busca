// console.log("iniciar consulta...");

const documentsSugestoes = [
    {
        termo: "AC3",
        total: "72",
    },
    {
        termo: "KIT VASO",
        total: "51",
    },
    {
        termo: "AC1",
        total: "42",
    },
    {
        termo: "COZINHA",
        total: "39",
    },
    {
        termo: "FANI",
        total: "37",
    },
    {
        termo: "AC2",
        total: "36",
    },
    {
        termo: "SAVEIRO",
        total: "29",
    },
    {
        termo: "VASO SANITARIO",
        total: "28",
    },
    {
        termo: "PORCELANATO",
        total: "25",
    },
    {
        termo: "POR PORTINARI",
        total: "25",
    },
];

// console.log(teste);
/* FORMA 1 */
sugestoesAoClicarNoInput();

/* FORMA 2 */
sugestoesAoBuscarNoInput();

function sugestoesAoClicarNoInput() {
    /* === INICIO SUGESTOES === */
    const input = document.getElementById("input_search");
    const suggestion = document.getElementsByClassName("input-suggestion")[0];

    // input.addEventListener("focusout", function () {
    //     suggestion.style.display = "none";
    //     console.log("ocultar");
    // });

    input.addEventListener("focus", function () {
        suggestion.style.display = "block";
        let html = [];
        for (let data of documentsSugestoes) {
            // console.log(data);
            html.push(`<li>
                    <div class="header">
                      <i class="ph-magnifying-glass"></i>
                      <p>${data.termo}</p>
                    </div>
                  </li>`);
            // html.push(
            //   `<div class="autocomplete__item">${data.title}</div>`
            // );
        }
        $(".maisbuscados ul").html(html.join(""));
    });

    /* === FIM SUGESTOES === */
}

function sugestoesAoBuscarNoInput() {
    const filterElement = document.querySelector("header input");
    const suggestion = document.getElementsByClassName("input-suggestion")[0];

    filterElement.addEventListener("focusout", function () {
        suggestion.style.display = "none";
        console.log("ocultar");
    });

    // Função para executar a consulta após o tempo de espera
    function doQuery() {
        let filterText = filterElement.value.toLowerCase();

        console.log("Consultando...");

        $.ajax({
            url: "/ajax/produtos",
            type: "GET",
            data: {
                q: filterText,
            },
            success: function (response) {
                // console.log(response);

                /* MOSTRAR sugestoes */
                suggestion.style.display = "block";

                let html = [];

                if (response.length) {
                    html.push(`<h5>sugestoes de busca</h5>`);

                    html.push(`<div class="product-list">`);

                    // Agora você pode percorrer o array de forma mais simples
                    response.forEach((item) => {
                        const referencia = item.referencia;
                        const nome_original = item.nome_original;
                        const nome_amigavel = item.nome_amigavel;
                        const fornecedor = item.fornecedor;
                        const departamento = item.departamento;
                        const categoria = item.categoria;
                        const subcategoria = item.subcategoria;
                        const imagem = item.imagem ? item.imagem : "sem-foto.jpg";

                        html.push(`<div class="product">
                                    <img src="${imagem}" alt="${nome_original}">
                                    <h3>${nome_amigavel ?? nome_original}</h3>
                                    <p>${referencia} - ${fornecedor}</p>
                                    <p>${departamento} | ${categoria} | ${subcategoria}</p>
                                </div>`);
                    });

                    html.push(`</div>`);
                } else {
                    suggestion.style.display = "none";
                }

                $(".load").html("");
                $(".autocomplete main").html(html.join(""));
            },
            error: function (response) {
                console.log(response);
            },
        });
    }

    // Timer para aguardar um período curto após a última tecla digitada
    let typingTimer;
    const doneTypingInterval = 2000; // Tempo em milissegundos

    // Evento keyup no elemento de input
    filterElement.addEventListener("keyup", (event) => {
        clearTimeout(typingTimer); // Limpa o timer anterior

        const valorDigitado = event.target.value;

        if (valorDigitado.length >= 3) {
            /* MOSTRAR O RESULTADO DA BUSCA QUANDO TIVER FOCO */
            filterElement.addEventListener("focusin", function () {
                suggestion.style.display = "block";
                console.log("mostrar");
            });

            $(".load").html('<img src="load2.gif" >');
            // Configura um novo timer após a última tecla pressionada
            typingTimer = setTimeout(doQuery, doneTypingInterval);
        }
    });
}
