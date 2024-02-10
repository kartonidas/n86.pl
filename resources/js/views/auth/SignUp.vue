<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email, sameAs } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import UserService from '@/service/UserService';
    
    export default {
        setup() {
            setMetaTitle('meta.title.signup');
            
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
                saving: false,
                register: {
                    email: '',
                    accept_regulation: false,
                    accept_privacy_policy: false,
                },
                errors: []
            }
        },
        validations () {
            return {
                register: {
                    email: { required, email },
                    accept_regulation : { sameAs: sameAs(true) },
                    accept_privacy_policy : { sameAs: sameAs(true) },
                }
            }
        },
        methods: {
            async registerAccount() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.saving = true
                    
                    this.userService.register(this.register)
                        .then(
                            (response) => {
                                this.saving = false
                                this.router.push({name: 'signup-success'})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        );
                }
            },
        }
    }
</script>

<template>
    <form v-on:submit.prevent="registerAccount">
        <h5 class="mb-5 text-center">{{ $t('register.register') }}</h5>
        <div class="mb-4">
            <label for="email" class="block text-900 text-lg font-medium mb-2">{{ $t('register.email') }}</label>
            <InputText id="email" type="text" :placeholder="$t('register.email_address')" class="w-full" :class="{'p-invalid' : v$.register.email.$error}" v-model="register.email" :disabled="saving"/>
            <div v-if="v$.register.email.$dirty">
                <p v-for="error of v$.register.email.$errors" :key="error.$uid">
                    <small class="p-error">{{ error.$message }}</small>
                </p>
            </div>
        </div>
        
        <div class="mb-2">
            <div class="flex align-items-center">
                <Checkbox v-model="register.accept_regulation" inputId="accept_regulation" :binary="true" :invalid="v$.register.accept_regulation.$error" />
                <label for="accept_regulation" class="ml-2">
                    {{ $t('register.accept') }} <a :href="$t('register.regulations_url')" target="_blank">{{ $t('register.regulations') }}</a>
                </label>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex align-items-center">
                <Checkbox v-model="register.accept_privacy_policy" inputId="accept_privacy_policy" :binary="true" :invalid="v$.register.accept_privacy_policy.$error" />
                <label for="accept_privacy_policy" class="ml-2">
                    {{ $t('register.accept') }} <a :href="$t('register.privacy_policy_url')" target="_blank">{{ $t('register.privacy_policy') }}</a>
                </label>
            </div>
        </div>

        <Message severity="error" :closable="false" v-if="errors.length">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        
        <Button type="submit" :label="$t('register.create_account')" :loading="saving" iconPos="right" class="w-full p-3 text-lg text-center"></Button>
    </form>
</template>
