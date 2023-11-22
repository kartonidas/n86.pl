<script>
    import { ref } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import UserService from '@/service/UserService';
    
    import { getResponseErrors } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required, sameAs } from '@/utils/i18n-validators'
    
    export default {
        setup() {
            const userService = new UserService()
            const router = useRouter()
            const route = useRoute()
            
            return {
                userService,
                router,
                route,
                v$: useVuelidate()
            }
        },
        data() {
            return {
                loading: false,
                password: '',
                confirm_password: '',
                errors: []
            }
        },
        validations () {
            return {
                password: { required },
                confirm_password: { required, sameAsPassword: sameAs(this.password) },
            }
        },
        methods: {
            async resetPassword() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.loading = true
                    this.userService.resetPassword(this.route.query.token, this.route.query.email, this.password)
                        .then(
                            (response) => {
                                this.loading = false
                                this.router.push({name: 'reset-password-success'})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.loading = false
                            }
                        );
                }
            },
        }
    }
</script>

<template>
    <div class="w-full py-6 px-5 sm:px-8">
        <div>
            <h3 class="mb-5">
                {{ $t('app.remind_password') }}
            </h3>
            <div class="mb-4">
                <label for="password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.password') }}</label>
                <Password id="password" v-model="password" :placeholder="$t('app.password')" :feedback="false" :class="{'p-invalid' : v$.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full md:w-30rem" :disabled="loading"></Password>
                <div v-if="v$.password.$dirty">
                    <p v-for="error of v$.password.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
            </div>
                
            <div class="mb-4">
                <label for="confirm_password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.repeat_password') }}</label>
                <Password id="confirm_password" v-model="confirm_password" :placeholder="$t('app.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full md:w-30rem" :disabled="loading"></Password>
                <div v-if="v$.confirm_password.$dirty">
                    <p v-for="error of v$.confirm_password.$errors" :key="error.$uid">
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
            
            <Button :label="$t('app.remind_password')" :loading="loading" iconPos="right" @click="resetPassword" class="w-full p-3 text-xl text-center"></Button>
        </div>
    </div>
</template>
