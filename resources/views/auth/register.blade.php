@extends('layouts.app')

@section('title', 'Cadastro - EventFlow')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Criar Conta</h4>
                    <div id="register-app">
                        <form @submit.prevent="register">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input id="name" type="text" class="form-control" v-model="form.name" required />
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" class="form-control" v-model="form.email" required />
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input id="password" type="password" class="form-control" v-model="form.password" required />
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                                <input id="password_confirmation" type="password" class="form-control" v-model="form.password_confirmation" required />
                            </div>

                            <!-- Mensagem de erro -->
                            <div v-if="error" class="alert alert-danger" role="alert">
                                @{{ error }}
                            </div>

                            <!-- Mensagem de sucesso -->
                            <div v-if="success" class="alert alert-success" role="alert">
                                @{{ success }}
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const { reactive, ref } = Vue;

        Vue.createApp({
            setup() {
                const form = reactive({
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                });

                const error = ref('');
                const success = ref('');

                const register = async () => {
                    error.value = '';
                    success.value = '';

                    if (form.password !== form.password_confirmation) {
                        error.value = 'As senhas não coincidem.';
                        return;
                    }
                    try {
                        const response = await axios.post('/api/register', form);
                        success.value = response.data.message + ' Redirecionando para login...';
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    } catch (e) {
                        if (e.response?.data?.errors) {
                            error.value = Object.values(e.response.data.errors)[0][0];
                        } else if (e.response?.data?.message) {
                            error.value = e.response.data.message;
                        } else {
                            error.value = 'Erro ao realizar cadastro. Tente novamente.';
                        }
                    }
                };

                return { form, error, success, register };
            }
        }).mount('#register-app');
    </script>
@endpush
