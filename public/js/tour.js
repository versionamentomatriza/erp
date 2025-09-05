$(function () {
    // console.clear()
    setTimeout(() => {

        if ($("#step1").length && $("#step3").length && $("#step4").length && $("#step5").length && $("#step6").length){
            let toutVar = window.localStorage.getItem('tour-app-sym');
            if(!toutVar){

                var tour = new Tour(steps);
                tour.show();
                window.localStorage.setItem('tour-app-sym', true);
            }
        }
    }, 100);
});

var steps = [
{
  title: "Bem-vindo!",
  content: "<p>Esse é o seu primeiro acesso, um breve tour sobre o sistema.</p>"
}, 
{
  id: "step1",
  content: "<p>Apresentamos de início o ambiente de emissão, endereço de IP atribuído, plano utilizado, sua data de expiração  e opção de upgrade disponível.</p>"
},
{
  id: "step3",
  content: "<p>Aqui estão os detalhes da sua conta, configurações personalizadas e a opção para sair.</p>"
},
{
  id: "step4",
  content: "<p>Utilize o menu lateral para navegar pelas diferentes telas do sistema.</p>"
},
{
  id: "step5",
  content: "<p>Aqui, você pode configurar sua empresa. Forneça os dados do emitente, faça o upload do certificado digital, se necessário para emissão fiscal, selecione a natureza da operação e forneça outras informações relevantes.</p>"
},
{
  id: "step6",
  content: "<p>Registre seus clientes, fornecedores e usuários nesta seção.</p>"
}, 
{
  id: "step7",
  content: "<p>Registre seus produtos, categorias, marcas, controle de estoque e padrões de tributação nesta área.</p>"
}, 
{
  title: "Obrigado!",
  content: "<p>Esperamos que tenha uma excelente experiência com o nosso sistema.</p>"
}
];

$('#click-tour').click(() => {

    if ($("#step1").length && $("#step3").length && $("#step4").length && $("#step5").length && $("#step6").length){
        var tour = new Tour(steps);
        tour.show();
    }
})

