<script>
    import { ref } from 'vue'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import RegisterForm from './_Form.vue'
    import UserInvoiceService from '@/service/UserInvoiceService'
    
    export default {
        components: { RegisterForm },
        setup() {
            setMetaTitle('meta.title.sale_registrer_new')
            
            const register = ref({
                continuation : 'month',
                type: 'invoice'
            })
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                register
            }
        },
        data() {
            return {
                saving: false,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.sale_registries'), route : { name : 'sale_register'} },
                        {'label' : this.$t('customer_invoices.new_sale_register'), disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createSaleRegister() {
                this.saving = true
                this.errors = []
                
                this.userInvoiceService.saleRegisterCreate(this.register)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('customer_invoices.sale_register_added'),
                            });
                            
                            this.$router.push({name: 'sale_register_edit', params: { saleRegisterId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            back() {
                this.$goBack('sale_register');
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('customer_invoices.new_sale_register') }}</h4>
        <RegisterForm @submit-form="createSaleRegister" @back="back" :register="register" :saving="saving" :errors="errors" />
    </div>
</template>