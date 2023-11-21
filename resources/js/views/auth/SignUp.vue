<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import UserService from '@/service/UserService';
    
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
                errors: []
            }
        },
        validations () {
            return {
                email: { required, email },
            }
        },
        methods: {
            async register() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.loading = true
                    this.userService.register(this.email)
                        .then(
                            (response) => {
                                this.loading = false
                                this.router.push({name: 'signup-success'})
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
                {{ $t('app.register') }}
            </h3>
            <div class="mb-4">
                <label for="email" class="block text-900 text-xl font-medium mb-2">{{ $t('app.email') }}</label>
                <InputText id="email" type="text" :placeholder="$t('app.email_address')" class="w-full" :class="{'p-invalid' : v$.email.$error}" v-model="email" />
                <div v-if="v$.email.$dirty">
                    <p v-for="error of v$.email.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
            </div>

            <Message severity="error" :closable="false" v-if="errors.length">
                <ul class="list-unstyled">
                    <li v-for="error of errors">
                        {{ error }}
                    </li>
                </ul>
            </Message>
            
            <Button :label="$t('app.create_account')" :loading="loading" iconPos="right" @click="register" class="w-full p-3 text-xl text-center"></Button>
        </div>
    </div>
</template>
