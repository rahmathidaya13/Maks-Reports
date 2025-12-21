<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
const form = useForm({
    email: "",
    password: "",
    remember: false,
    // 'g-recaptcha-response': '', // reCAPTCHA token
});
const isSubmit = () => {
    // form['g-recaptcha-response'] = document.querySelector(
    //     '[name="g-recaptcha-response"]'
    // )?.value
    form.post(route("login.store"), {
        onFinish: () => form.reset("password")
    });
};
const showPassword = ref(false);
const togglePasswordVisibilty = () => {
    showPassword.value = !showPassword.value;
};
const page = usePage();
const message = computed(() => page.props.flash.message);
onMounted(() => {
    if (typeof grecaptcha !== 'undefined' && document.getElementById('recaptcha-box') && document.getElementById('recaptcha-box').innerHTML === '') {
        grecaptcha.render('recaptcha-box', {
            sitekey: page.props.recaptcha_site_key, // Ambil dari props
        });
    }
})
const inputEmail = ref(null);
onMounted(() => {
    inputEmail.value.focus();
})
</script>
<template>

    <Head title="Login" />
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-xl-5">
                <alert :duration="10"
                    :variant="$page.props.flash.error || $page.props.errors.error ? 'danger' : 'success'"
                    :message="$page.props.flash.error || message || $page.props.errors.error" />
                <div class="card overflow-auto shadow-sm rounded rounded-4 p-xl-4 p-0">
                    <div class="card-title text-center mb-0 py-3">
                        <h2 class="fw-bold text-capitalize">Masuk</h2>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-grid mb-3 align-items-center">
                            <a :href="route('google.login')" class="btn btn-app">
                                <img src="/storage/icon/google.svg" class="img-fluid img-icon" alt="">
                                Masuk Dengan Google
                            </a>
                        </div>
                        <hr />
                        <form-wrapper @submit="isSubmit">
                            <div class="mb-3">
                                <input-label class="fw-semibold" for="email" value="Email" />
                                <div class="position-relative">
                                    <i class="fas fa-envelope input-icon-left"></i>
                                    <text-input ref="inputEmail" autocomplete="email" placeholder="Email"
                                        class="input-fixed-height" type="email" name="email" v-model="form.email" />
                                </div>
                                <input-error :message="form.errors.email" />
                            </div>
                            <div class="mb-3">
                                <input-label class="fw-semibold" for="password" value="Kata Sandi" />
                                <div class="position-relative">
                                    <i class="fas fa-lock input-icon-left"></i>
                                    <text-input :type="showPassword ? 'text' : 'password'" placeholder="*******" :class="[
                                        'input-fixed-height',
                                        'has-icon-right',
                                    ]" autocomplete="off" v-model="form.password" name="password" />
                                    <i @click="togglePasswordVisibilty" :class="[
                                        'input-icon-right',
                                        'cursor-pointer',
                                        showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye'
                                    ]"></i>
                                </div>
                                <input-error :message="form.errors.password" />
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <check-box :value="form.remember" v-model:checked="form.remember" name="remember"
                                    label="Ingatkan saya" />
                                <div>
                                    <Link :href="route('forgot.create')" class="text-decoration-none">Lupa kata sandi?
                                    </Link>
                                </div>
                            </div>

                            <!-- <div class="mb-4">
                                <div id="recaptcha-box" class="g-recaptcha"
                                    :data-sitekey="$page.props.recaptcha_site_key"></div>
                                <input-error :message="form.errors['g-recaptcha-response'] || form.errors.recaptcha" />
                            </div> -->

                            <div class="mb-3 d-grid">
                                <base-button :loading="form.processing" class="bg-gradient btn-height-1"
                                    variant="primary" type="submit" name="submit" label="Masuk"
                                    waiting="Memproses..." />
                            </div>
                        </form-wrapper>

                        <div class="mb-3 d-flex justify-content-center align-items-center px-2 gap-1">
                            <span>
                                Belum punya akun?
                            </span>
                            <span>
                                <Link :href="route('register')" class="text-decoration-none">Daftar</Link>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
