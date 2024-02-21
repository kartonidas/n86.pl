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
            setMetaTitle('meta.title.customer_invoices_new')
            
            const invoice = ref({
                type: "invoice",
                document_date: new Date(),
                sell_date: new Date(),
                payment_date: new Date(),
                created_user_id: appStore().userId,
                currency: "PLN",
                language: "pl",
                items: [{
                    quantity: 1,
                    net_amount: 0,
                    total_net_amount: 0,
                    gross_amount: 0,
                    total_gross_amount: 0,
                    vat_value: "23"
                }],
                net_amount: 0,
                gross_amount: 0,
            })
            
            const userInvoiceService = new UserInvoiceService()
            
            return {
                userInvoiceService,
                invoice
            }
        },
        data() {
            return {
                saving: false,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settlements'), disabled : true },
                        {'label' : this.$t('menu.customer_invoices'), route : { name : 'customer_invoices'} },
                        {'label' : this.$t('customer_invoices.new_invoice'), disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createInvoice() {
                this.saving = true
                this.errors = []
                
                let invoice = Object.assign({}, this.invoice);
                if(invoice.document_date && invoice.document_date instanceof Date)
                    invoice.document_date = moment(invoice.document_date).format("YYYY-MM-DD")
                if(invoice.sell_date && invoice.sell_date instanceof Date)
                    invoice.sell_date = moment(invoice.sell_date).format("YYYY-MM-DD")
                if(invoice.payment_date && invoice.payment_date instanceof Date)
                    invoice.payment_date = moment(invoice.payment_date).format("YYYY-MM-DD")
                
                this.userInvoiceService.invoiceCreate(invoice)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('customer_invoices.invoice_added'),
                            });
                            
                            this.$router.push({name: 'customer_invoices_edit', params: { customerInvoiceId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            back() {
                this.$goBack('customer_invoices');
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="settlement" mark="settlement:invoice" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('customer_invoices.new_customer_invoice') }}</h4>
        <InvoiceForm @submit-form="createInvoice" @back="back" :invoice="invoice" :saving="saving" :errors="errors" />
    </div>
</template>