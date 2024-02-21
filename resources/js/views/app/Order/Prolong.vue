<script>
    import { getPrices, getResponseErrors, setMetaTitle, getLocale } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import OrderService from '@/service/OrderService'
    import moment from 'moment'
    
    export default {
        setup() {
            setMetaTitle('meta.title.prolong_access')
            
            const orderService = new OrderService()
            
            return {
                v: useVuelidate(),
                orderService,
            }
        },
        data() {
            return {
                invoicingOk : true,
                errors: [],
                items: 0,
                end: 0,
                end_date: null,
                disabled: true,
                order: {
                    period: "p1",
                },
                locale: getLocale(),
                saving: false,
                prices: getPrices(),
            }
        },
        beforeMount() {
            this.orderService.validateInvoicingData()
                .then(
                    (response) => {
                        this.invoicingOk = response.data.valid
                        this.disabled = !this.invoicingOk
                    },
                    (errors) => {},
                )
            this.orderService.getActiveSubscription()
                .then(
                    (response) => {
                        if(response.data.start == undefined)
                            this.$router.push({name: 'order'})
                        else
                        {
                            this.items = response.data.items
                            this.end = response.data.end
                            this.end_date = response.data.end_date
                        }
                    },
                    (errors) => {},
                )
        },
        methods: {
            async submitForm() {
                const result = await this.v.$validate()
                if (result) {
                    this.saving = true;
                    this.orderService.prolong(this.order)
                        .then(
                            (response) => {
                                window.location.href = response.data.url;
                                this.saving = false;
                            },
                            (errors) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(errors)
                                this.saving = false
                            }
                        )
                }
            },
            calculateTotal() {
                return this.items * this.prices[this.order.period].price_vat
            },
            calculateEnd() {
                let month = this.order.period == "p12" ? 12 : 1;
                var currentEndDate = moment(this.end_date);
                currentEndDate.add(month, 'month');
                return currentEndDate.format("YYYY-MM-DD HH:mm:ss");
            },
            extend() {
                this.$router.push({name: 'order_extend'})
            }
        },
        validations () {
            return {
                order: {
                    period: { required },
                }
            }
        },
    }
</script>
<template>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="package" mark="package:prolong" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('orders.prolong_access') }}</h4>
        
        <template v-if="!invoicingOk">
            <Message severity="error" :closable="false" class="mb-5">
                {{ $t('orders.invoice_data_empty') }}<router-link :to="{name: 'firm_data'}">{{ $t('orders.invoice_data_empty_here') }}</router-link>.
            </Message>
        </template>
        
        <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
            <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                <ul class="list-unstyled">
                    <li v-for="error of errors">
                        {{ error }}
                    </li>
                </ul>
            </Message>
            
            <div class="grid">
                <div class="col-12 line-height-3 text-sm">
                    {{ $t('orders.prolong_extend_info_line_1' )}}
                    <br/>
                    {{ $t('orders.prolong_extend_info_line_2' )}}
                    <router-link :to="{name: 'order_extend'}">{{ $t('orders.prolong_extend_info_line_3' )}}</router-link>.
                </div>
                
                <div class="col-12 sm:col-8">
                    <div class="mb-6">
                        {{ $t('orders.select_period_prolong') }}:
                        <ul class="list-unstyled">
                            <li v-for="(price, key) in prices" class="pt-1 pb-1 flex align-items-center">
                                <RadioButton v-model="order.period" name="period" :inputId="price.name" :value="key" :disabled="disabled" />
                                <label :for="price.name" class="ml-2">
                                    <span class="font-medium">{{ price[locale].name }}</span>
                                </label>
                            </li>
                        </ul>
                        <div v-if="v.order.period.$error" class="ml-1">
                            <small class="p-error">{{ v.order.period.$errors[0].$message }}</small>
                        </div>
                        
                        <div class="mt-5 line-height-3 font-medium">
                            {{ $t('orders.period_end_date_info', [end_date, calculateEnd()]) }}
                        </div>
                        
                        <div class="mt-5">
                            <Button type="button" :label="$t('orders.extend_package')" @click="extend" severity="info" :loading="saving" class="w-auto text-center" size="small"></Button>
                        </div>
                    </div>
                </div>
                <div class="col-12 sm:col-4">
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.period') }}:</span>
                        <div class="text-xl">
                            {{ prices[order.period][locale].name }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.active_items') }}:</span>
                        <div class="text-xl">
                            {{ items }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.price_per_item') }}:</span>
                        <div class="text-xl">
                            {{ numeralFormat(prices[order.period].price_vat, '0.00') }} PLN
                            <small class="text-sm" v-if="order.period == 'p1'">/{{ $t('orders.month') }}</small>
                            <small class="text-sm" v-if="order.period == 'p12'">/{{ $t('orders.year') }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.total_to_paid') }}:</span>
                        <div class="text-2xl">
                            {{ numeralFormat(calculateTotal(), '0.00') }} PLN
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <Button type="submit" :label="$t('orders.order')" severity="danger" :loading="saving" class="w-full text-center" :disabled="disabled"></Button>
                        <small>{{ $t('orders.price_tax_info', [prices.p1.vat]) }}.</small>
                        <small class="block mt-1">
                            {{ $t('orders.bottom_info') }}
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>