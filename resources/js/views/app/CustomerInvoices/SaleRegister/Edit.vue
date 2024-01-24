<script>
    import { ref } from 'vue'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import RegisterForm from './_Form.vue'
    import UserInvoiceService from '@/service/UserInvoiceService'
    
    export default {
        components: { RegisterForm },
        setup() {
            setMetaTitle('meta.title.sale_registrer_edit')
            
            const register = ref({
                continuation : 'month',
                type: 'invoice'
            })
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                register,
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.sale_registries'), route : { name : 'sale_register'} },
                        {'label' : this.$t('customer_invoices.edit_sale_register'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.userInvoiceService.saleRegisterGet(this.$route.params.saleRegisterId)
                .then(
                    (response) => {
                        this.register = response.data
                        this.loading = false
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateSaleRegister() {
                this.saving = true
                this.errors = []
                
                this.userInvoiceService.saleRegisterUpdate(this.$route.params.saleRegisterId, this.register)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customer_invoices.sale_register_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(errors)
                            this.saving = false
                        }
                )
            },
            back() {
                this.$router.push({name: 'sale_register'})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('customer_invoices.edit_sale_register') }}</h4>
        <RegisterForm @submit-form="updateSaleRegister" @back="back" :register="register" source="edit" :saving="saving" :loading="loading" :errors="errors" />
    </div>
</template>