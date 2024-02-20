<script>
    import { ref } from 'vue'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import InvoiceForm from './_Form.vue'
    import UserInvoiceService from '@/service/UserInvoiceService'
    import moment from 'moment'
    
    export default {
        components: { InvoiceForm },
        setup() {
            setMetaTitle('meta.title.customer_invoices_edit')
            
            const invoice = ref({})
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                invoice
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                block: false,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.customer_invoices'), route : { name : 'customer_invoices'} },
                        {'label' : this.$t('customer_invoices.edit_invoice'), disabled : true },
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
            this.userInvoiceService.invoiceGet(this.$route.params.customerInvoiceId)
                .then(
                    (response) => {
                        this.invoice = response.data
                        this.block = !this.invoice.can_update
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
            async updateInvoice() {
                this.saving = true
                this.errors = []
                
                let invoice = Object.assign({}, this.invoice);
                if(invoice.document_date && invoice.document_date instanceof Date)
                    invoice.document_date = moment(invoice.document_date).format("YYYY-MM-DD")
                if(invoice.sell_date && invoice.sell_date instanceof Date)
                    invoice.sell_date = moment(invoice.sell_date).format("YYYY-MM-DD")
                if(invoice.payment_date && invoice.payment_date instanceof Date)
                    invoice.payment_date = moment(invoice.payment_date).format("YYYY-MM-DD")
                
                this.userInvoiceService.invoiceUpdate(this.$route.params.customerInvoiceId, invoice)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customer_invoices.invoice_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            back() {
                this.$goBack('customer_invoices', true);
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('customer_invoices.new_customer_invoice') }}</h4>
        <InvoiceForm @submit-form="updateInvoice" @back="back" :invoice="invoice" :block="block" source="edit" :saving="saving" :errors="errors" />
    </div>
</template>