@extends('layouts.header_auth', ['title' => 'Login'])

@section('content')

    @php
        $login = (isset($_COOKIE['ckLogin'])) ? base64_decode($_COOKIE['ckLogin']) : '';
        $pass = (isset($_COOKIE['ckPass'])) ? base64_decode($_COOKIE['ckPass']) : '';
        $remember = (isset($_COOKIE['ckRemember'])) ? ($_COOKIE['ckRemember']) : '';
    @endphp

    @section('css')
        <style type="text/css">
            /* Estilos gerais */
            body,
            html {
                height: 100%;
                margin: 0;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                /* Evitar overflow na página */
            }

            /* Container principal */
            .auth-fluid {
                display: flex;
                flex: 1;
                height: 100vh;
                position: relative;
            }

            /* Parte esquerda com imagem de fundo e overlay */
            .auth-fluid-left {
                flex: 1;
                background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('/assets/images/bg-auth.jpg') no-repeat center center;
                background-size: cover;
                min-height: 100%;
                /* Garante que a imagem cubra toda a tela */
                display: none;
                /* Oculta em telas pequenas */
            }

            /* Parte direita com o formulário */
            .auth-fluid-form-box {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 3rem;
                max-width: 480px;
                background-color: rgba(255, 255, 255, 0.9);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                width: 100%;
                height: auto;
                overflow-y: auto;
                /* Habilita rolagem vertical */
            }

            /* Ajustes do formulário */
            .card-body {
                width: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            /* Ajuste da logo no topo */
            .auth-brand {
                text-align: center;
                margin-bottom: 20px;
            }

            .auth-brand img {
                width: 300px;
                margin: 0 auto;
                /* Centraliza a logo */
                display: block;
            }

            .auth-fluid .auth-fluid-left::before,
            .auth-fluid .auth-fluid-right::before {
                background: none !important;
            }

            /* Footer ajustado logo abaixo do formulário */
            .footer-alt {
                text-align: center;
                margin-top: 20px;
                width: 100%;
            }

            /* Responsividade para dispositivos móveis */
            @media (max-width: 768px) {
                .auth-fluid {
                    flex-direction: column;
                }

                .auth-fluid-form-box {
                    max-width: 100%;
                    padding: 15px;
                    overflow-y: auto;
                    /* Habilita rolagem em telas pequenas */
                }

                .auth-fluid-left {
                    display: none;
                    /* Oculta a imagem de fundo em dispositivos móveis */
                }
            }

            @media (min-width: 769px) {
                .auth-fluid-left {
                    display: block;
                    /* Exibe a imagem de fundo em dispositivos maiores */
                }

                .auth-fluid-form-box {
                    width: auto;
                    margin: 0 auto;
                    /* Centraliza o formulário horizontalmente */
                }
            }
        </style>
    @endsection
    <div class="auth-fluid">

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-inner">

                    </div>
                </div>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->

        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">

            <div class="card-body d-flex flex-column h-100 gap-3">

                <!-- Logo -->
                <div class="auth-brand text-center text-lg-start logo-mob">
                    <span><img style="width: 300px" src="/logo.png" alt="dark logo"></span>
                </div>

                <div class="my-auto">
                    <!-- title-->
                    @if(env("APP_ENV") == "demo")
                        <div class="card">
                            <div class="card-body">
                                <p>Clique nos botões abaixo para acessar os usuários pré configurados!</p>
                                <div class="row">
                                    <div class="col-12 col-lg-6 mt-1">
                                        <button class="btn btn-success w-100" onclick="login('matriza@matriza.com', '123456')">
                                            SUPERADMIN
                                        </button>
                                    </div>
                                    <div class="col-12 col-lg-6 mt-1">
                                        <button class="btn btn-dark w-100" onclick="login('teste@teste.com', '123456')">
                                            ADMNISTRADOR
                                        </button>
                                    </div>
                                </div>
                            </div>
                    @endif
                        <h4 class="mt-0">Login</h4>
                        <p class="text-muted mb-4">Digite seu endereço de email e senha para acessar a conta.</p>

                        <!-- form -->
                        <form method="POST" action="{{ route('login') }}" id="form-login">
                            @csrf

                            <div class="mb-3">
                                <label for="emailaddress" class="form-label">Email</label>
                                <input class="form-control" type="email" name="email" id="email" required
                                    value="{{ $login }}" placeholder="Digite seu email">
                            </div>
                            <div class="mb-3">
                                <a href="{{ route('password.request') }}" class="text-muted float-end"><small>Esqueceu sua
                                        senha?</small></a>
                                <label for="password" class="form-label">Senha</label>
                                <input class="form-control" type="password" name="password" required value="{{ $pass }}"
                                    id="password" placeholder="Digite sua senha">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input name="remember" type="checkbox" {{ $remember ? 'checked' : '' }}
                                        class="form-check-input" id="checkbox-signin">
                                    <label class="form-check-label" for="checkbox-signin">lembrar-me</label>
                                </div>


                            </div>

                            @if(Session::has('error'))
                                <div class="alert alert-danger">{{ Session::get('error') }}</div>
                            @endif

                            @if(Session::has('success'))
                                <div class="alert alert-success">{{ Session::get('success') }}</div>
                            @endif
                            <div class="d-grid mb-0 text-center">
                                <button class="btn btn-primary" type="submit"><i class="ri-login-box-line"></i> Acessar
                                </button>
                            </div>
                            <!-- social-->

                        </form>
                        <!-- end form-->
                    </div>


                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Não tem uma conta? <a href="{{ route('register') }}"
                                class="text-muted ms-1"><b>Inscrever-se</b></a></p>
                    </footer>

                    <div class="text-center mt-2">
						<a target="_blank" 
						   href="https://api.whatsapp.com/send?phone=554832081006&text=Ol%C3%A1!%20Gostaria%20de%20mais%20informa%C3%A7%C3%A3o%0a%20Estou%20na%20p%C3%A1gina:%20https://matriza.com.br/{{ env('APP_FONE') }}" 
						   style="color: #2559d3ff; font-weight:  text-decoration: none; font-size: 14px;">
						   <i class="ri-whatsapp-fill" style="color: #25D366; font-size: 16px; margin-right: 5px;"></i> Fale Conosco
						</a>
					</div>

                </div> <!-- end .card-body -->
            </div>
            <!-- end auth-fluid-form-box-->
        </div>
@endsection

    @section('js')

        <script type="text/javascript">
            function login(email, senha) {
                $('#email').val(email)
                $('#password').val(senha)
                $('#form-login').submit()
            }
        </script>
    @endsection