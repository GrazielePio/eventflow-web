@extends('layouts.app')

@section('title', 'Login - EventFlow')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Login</h4>
                    <div id="login-app">
                        <form @submit.prevent="login">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" class="form-control" v-model="form.email" required />
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input id="password" type="password" class="form-control" v-model="form.password" required />
                            </div>

                            <div v-if="error" class="alert alert-danger">@{{ error }}</div>

                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="/register">Não tem conta? Cadastre-se</a>
                        </div>
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
                const form = reactive({ email: '', password: '' });
                const error = ref('');

                const login = async () => {
                    error.value = '';
                    try {
                        const response = await axios.post('/api/login', form);
                        localStorage.setItem('token', response.data.token);
                        window.location.href = '/home';
                    } catch (e) {
                        error.value = e.response?.data?.message || 'Erro ao fazer login';
                    }
                };

                return { form, error, login };
            }
        }).mount('#login-app');
    </script>
@endpush
