<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import UserService from '@/service/UserService'
    
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'

    export default {
        setup() {
            const userService = new UserService()
            const router = useRouter()
            
            return {
                userService,
                router,
                v$: useVuelidate()
            }
        },
        data() {
            return {
                loading: false,
                email: '',
                password: '',
                errors: []
            }
        },
        validations () {
            return {
                email: { required, email },
                password: { required },
            }
        },
        methods: {
            async login() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.loading = true
                    this.userService.login(this.email, this.password)
                        .then(
                            (response) => {
                                if (response.id != undefined)
                                    this.router.push({name: 'dashboard'})
                                this.loading = false
                            },
                            (errors) => {
                                this.getErrors(errors)
                                this.loading = false
                            }
                        );
                }
            },
            getErrors(errors) {
                for (var i in errors) {
                    errors[i].forEach((err) => {
                        this.errors.push(err);
                    });
                }
            }
        }
    }
</script>

<template>
    <div class="w-full py-6 px-5 sm:px-8">
        <div>
            <h3 class="mb-5">
                {{ $t('auth.login') }}
            </h3>
            <div class="mb-4">
                <label for="email" class="block text-900 text-xl font-medium mb-2">{{ $t('auth.email') }}</label>
                <InputText id="email" type="text" :placeholder="$t('auth.email_address')" class="w-full" :class="{'p-invalid' : v$.email.$error}" style="padding: 1rem" v-model="email" />
                <div v-if="v$.email.$dirty">
                    <p v-for="error of v$.email.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-900 font-medium text-xl mb-2">{{ $t('auth.password') }}</label>
                <Password id="password" v-model="password" :placeholder="$t('auth.password')" :feedback="false" :class="{'p-invalid' : v$.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :inputStyle="{ padding: '1rem' }"></Password>
                <div v-if="v$.password.$dirty">
                    <p v-for="error of v$.password.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
                <div class="text-right mt-1">
                    <router-link :to="{name: 'forgot-password'}">{{ $t('auth.forgot_password') }}</router-link>
                </div>
            </div>
            

            <Message severity="error" :closable="false" v-if="errors.length">
                <ul class="list-unstyled">
                    <li v-for="error of errors">
                        {{ error }}
                    </li>
                </ul>
            </Message>
            
            <Button :label="$t('auth.signin')" :loading="loading" iconPos="right" @click="login" class="w-full p-3 text-xl text-center"></Button>
            
            <div class="mt-4">
                {{ $t('auth.no_account_yet') }} <router-link :to="{name: 'signup'}">{{ $t('auth.create_account') }}</router-link>
            </div>
        </div>
    </div>
</template>
