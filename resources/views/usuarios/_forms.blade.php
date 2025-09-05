<div class="row g-2">

    {{-- Nome --}}
    <div class="col-md-3">
        {!! Form::text('name', 'Nome')
            ->attrs(['class' => 'form-control'])
            ->required()
        !!}
    </div>

    {{-- E-mail --}}
    <div class="col-md-3">
        <div style="position: relative;">
            {!! Form::text('email', 'Email')
                ->attrs([
                    'class' => 'form-control',
                    'style' => 'padding-left:1.5rem;',
                    'required' => true
                ])
                ->value(old('email', $item->email ?? ''))
            !!}

@if(isset($item))
    <span id="email-status" title="Status do e-mail"
        style="
            position: absolute;
            top: 70%;
            left: 10px;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            cursor: help;
        ">
    </span>
@endif
</div>
</div>

<script>
    const emailInput = document.querySelector('input[name="email"]');
    const emailStatus = document.getElementById('email-status');

    // Booleano vindo do backend
    const emailVerificado = @json(isset($item) && $item->email_verified_at !== null);

    function atualizarStatusEmail() {
        if (emailVerificado) {
            // Verde = já verificado
            emailStatus.style.backgroundColor = '#28a745';
            emailStatus.title = 'E-mail verificado';
        } else {
            if (emailInput.value.trim() === '') {
                // Vermelho = vazio/não verificado
                emailStatus.style.backgroundColor = '#dc3545';
                emailStatus.title = 'E-mail vazio ou não verificado';
            } else {
                // Amarelo = preenchido mas não verificado
                emailStatus.style.backgroundColor = '#ffc107';
                emailStatus.title = 'E-mail preenchido, mas não verificado';
            }
        }
    }

    if (emailInput && emailStatus) {
        atualizarStatusEmail();
        emailInput.addEventListener('input', atualizarStatusEmail);
    }
</script>


    {{-- Admin --}}
    <div class="col-md-2">
        {!! Form::select('admin', 'Admin', [0 => 'Não', 1 => 'Sim'])
            ->attrs(['class' => 'form-select'])
            ->required()
        !!}
    </div>

    {{-- Notificações --}}
    @if(__isNotificacao(Auth::user()->empresa))
        <div class="col-md-2">
            {!! Form::select('notificacao_cardapio', 'Notificação cardápio', [0 => 'Não', 1 => 'Sim'])
                ->attrs(['class' => 'form-select'])
                ->required()
            !!}
        </div>
    @endif

    @if(__isNotificacaoMarketPlace(Auth::user()->empresa))
        <div class="col-md-2">
            {!! Form::select('notificacao_marketplace', 'Notificação marketplace', [0 => 'Não', 1 => 'Sim'])
                ->attrs(['class' => 'form-select'])
                ->required()
            !!}
        </div>
    @endif

    @if(__isNotificacaoEcommerce(Auth::user()->empresa))
        <div class="col-md-2">
            {!! Form::select('notificacao_ecommerce', 'Notificação ecommerce', [0 => 'Não', 1 => 'Sim'])
                ->attrs(['class' => 'form-select'])
                ->required()
            !!}
        </div>
    @endif

    {{-- Senha --}}
    <div class="col-md-2">
        <label for="senha">Senha</label>
        <div class="input-group" id="show_hide_password">
            <input type="password" class="form-control" id="senha" name="password" autocomplete="off"
                @if(isset($senhaCookie)) value="{{ $senhaCookie }}" @endif>
            <a class="input-group-text"><i class="ri-eye-line"></i></a>
        </div>
    </div>

    {{-- Controle de acesso --}}
    <div class="col-md-3">
        {!! Form::select('role_id', 'Controle de acesso', ['' => 'Selecione'] + $roles->pluck('description', 'id')->all())
            ->attrs(['class' => 'select2'])
            ->value(isset($item) && $item->roles ? $item->roles->first()->id : null)
            ->required()
        !!}
    </div>

    {{-- Locais de acesso --}}
    @if(__countLocalAtivo() > 1)
        <div class="col-md-4">
            <label for="">Locais de acesso</label>
            <select required class="select2 form-control select2-multiple" data-toggle="select2" name="locais[]" multiple="multiple">
                @foreach(__getLocaisAtivos() as $local)
                    <option value="{{ $local->id }}"
                        @if(in_array($local->id, isset($item) ? $item->locais->pluck('localizacao_id')->toArray() : [])) selected @endif>
                        {{ $local->descricao }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" value="{{ __getLocalAtivo() ? __getLocalAtivo()->id : '' }}" name="local_id">
    @endif

    {{-- Escolher localização em compra/venda --}}
    <div class="col-md-3">
        {!! Form::select('escolher_localidade_venda', 'Escolher localização em compra e venda', [0 => 'Não', 1 => 'Sim'])
            ->attrs(['class' => 'form-select tooltipp'])
        !!}
        <div class="text-tooltip d-none">
            Marcar como sim se for escolher a localização ao realizar a venda, compra e devolução
        </div>
    </div>

    <hr>

    {{-- Imagem de perfil --}}
    <div class="card col-md-3 mt-3 form-input">
        <p>Selecione uma imagem de perfil</p>
        <div class="preview">
            <button type="button" id="btn-remove-imagem" class="btn btn-danger btn-sm">x</button>
            @isset($item)
                <img id="file-ip-1-preview" src="{{ $item->img }}">
            @else
                <img id="file-ip-1-preview" src="/imgs/no-image.png">
            @endisset
        </div>
        <label for="file-ip-1">Imagem</label>
        <input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
    </div>

    <hr class="mt-4">

    {{-- Botão salvar --}}
    <div class="col-12 text-end">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>

</div>

{{-- Modal confirmação de e-mail --}}
<div class="modal fade" id="emailConfirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmação de E-mail</h5>
            </div>
            <div class="modal-body">
                <p>Enviamos um link de confirmação para o seu e-mail.  
                   Verifique sua caixa de entrada para ativar sua conta.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-resend" disabled>
                    Reenviar link <span id="countdown"></span>
                </button>
                <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Ok</button>
            </div>
        </div>
    </div>
</div>

@if(session('show_confirmation_modal') && isset($item) && is_null($item->email_verified_at))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('emailConfirmationModal');
    const myModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    myModal.show();

    modalEl.querySelector('[data-bs-dismiss="modal"]').addEventListener('click', function() {
        myModal.hide();
    });
});
</script>
@endif

@section('js')
<script>
        function showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.textContent = message;

        // Estilos para centralizar na tela
        toast.style.position = 'fixed';
        toast.style.top = '50%';
        toast.style.left = '50%';
        toast.style.transform = 'translate(-50%, -50%)'; // centraliza exato
        toast.style.background = '#28a745'; // verde sucesso
        toast.style.color = '#fff';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '6px';
        toast.style.fontSize = '16px';
        toast.style.zIndex = 9999;
        toast.style.opacity = '1';
        toast.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
        toast.style.maxWidth = '80%';
        toast.style.textAlign = 'center';

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, duration);
    }


    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });
    });

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdownTime = 60; // segundos
    let btnResend = document.getElementById('btn-resend');
    let countdownSpan = document.getElementById('countdown');

    function startCountdown() {
        btnResend.disabled = true;
        let timer = setInterval(function() {
            countdownTime--;
            countdownSpan.textContent = `(${countdownTime}s)`;
            if (countdownTime <= 0) {
                clearInterval(timer);
                countdownSpan.textContent = '';
                btnResend.disabled = false;
            }
        }, 1000);
    }

    // Inicia contagem assim que abrir o modal
    @if(session('show_confirmation_modal'))
        startCountdown();
    @endif

    btnResend.addEventListener('click', function() {
        btnResend.disabled = true;
        countdownSpan.textContent = '(enviando...)';

        fetch('{{ route('usuarios.resend.confirmation', isset($item) ? $item->id : 0) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            countdownTime = 60;
            startCountdown();
            showToast(data.message);  // mostra o toast
        })
        .catch(() => {
            countdownSpan.textContent = '';
            btnResend.disabled = false;
            showToast('Erro ao reenviar e-mail.', 4000);
        });

    });
});
</script>
<script>
    const emailInput = document.querySelector('input[name="email"]');
    const emailStatus = document.getElementById('email-status');

    // Aqui pegamos do servidor se o email já foi verificado
    // Vai vir do Blade PHP e ser um booleano
    const emailVerificado = @json(isset($item) && $item->email_verified_at !== null);

    function atualizarStatusEmail() {
        if(emailVerificado) {
            // Se o e-mail já foi verificado, bolinha verde sempre
            emailStatus.style.backgroundColor = '#28a745'; // verde
            emailStatus.title = 'E-mail verificado';
        } else {
            // Senão, verifica se está preenchido ou vazio
            if(emailInput.value.trim() === '') {
                emailStatus.style.backgroundColor = '#dc3545'; // vermelho
                emailStatus.title = 'E-mail vazio ou não verificado';
            } else {
                emailStatus.style.backgroundColor = '#ffc107'; // amarelo, indica preenchido mas não verificado
                emailStatus.title = 'E-mail preenchido, mas não verificado';
            }
        }
    }

    if(emailInput && emailStatus) {
        atualizarStatusEmail();
        emailInput.addEventListener('input', atualizarStatusEmail);
    }
</script>

@endsection
