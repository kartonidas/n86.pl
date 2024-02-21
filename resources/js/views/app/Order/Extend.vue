<script>
    import { getPrices, getResponseErrors, setMetaTitle, getLocale } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required, minValue, maxValue } from '@/utils/i18n-validators'
    import Slider from 'primevue/slider';
    import OrderService from '@/service/OrderService'
    import moment from 'moment'
    
    export default {
        components: { Slider },
        setup() {
            setMetaTitle('meta.title.extend_access')
            
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
                days_to_end: 0,
                end_date: null,
                minItems: 1,
                maxItems: 1000,
                disabled: true,
                order: {
                    period: 'p1',
                    total: 10,
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
                            this.end_date = response.data.end_date
                            this.days_to_end = response.data.days_to_end
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
                    this.orderService.extend(this.order)
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
            calculateSingleItem() {
                let price = this.prices['p1'].price_day_vat
                if (this.days_to_end > 31) 
                    price = this.prices['p12'].price_day_vat
                    
                return price * this.days_to_end;
            },
            calculateTotal() {
                let price = this.calculateSingleItem()
                return this.order.total * price
            },
            calculateTotalItems() {
                return this.items + this.order.total;
            },
            prolong() {
                this.$router.push({name: 'order_prolong'})
            }
        },
        validations () {
            return {
                order: {
                    total: { required, minValue: minValue(1), maxValueValue: maxValue(1000) },
                }
            }
        },
    }
</script>
<template>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="package" mark="package:extend" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('orders.extend_access') }}</h4>
        
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
                    {{ $t('orders.extend_prolong_info_line_1') }}
                    <br/>
                    {{ $t('orders.extend_prolong_info_line_2' )}}
                    <router-link :to="{name: 'order_prolong'}">{{ $t('orders.extend_prolong_info_line_3' )}}</router-link>.
                </div>
                
                <div class="col-12 sm:col-8">
                    <div class="mb-6">
                        <div class="flex align-items-baseline">
                            <label for="type" class="block text-900 font-medium mb-2 mr-5 white-space-nowrap">
                                {{ $t('orders.total_items') }}:
                            </label>
                            <InputNumber v-model.number="order.total" :max="maxItems" style="width: 150px" :class="{'p-invalid' : v.order.total.$error}" :disabled="disabled"/>
                            <div v-if="v.order.total.$error" class="ml-1">
                                <small class="p-error">{{ v.order.total.$errors[0].$message }}</small>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <Slider v-model="order.total" :min="minItems" :max="maxItems" :useGrouping="false" locale="pl-PL" :disabled="disabled"/>
                        </div>
                    </div>
                    
                    <div class="mt-5 line-height-3 font-medium">
                        {{ $t('orders.extend_info', [items, calculateTotalItems(), end_date]) }}
                    </div>
                    
                    <div class="mt-5">
                        <Button type="button" :label="$t('orders.prolong_package')" @click="prolong" severity="info" :loading="saving" class="w-auto text-center" size="small"></Button>
                    </div>
                </div>
                <div class="col-12 sm:col-4">
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.active_items') }}:</span>
                        <div class="text-xl">
                            {{ order.total }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.price_per_item') }}:</span>
                        <div class="text-xl">
                            {{ numeralFormat(calculateSingleItem(), '0.00') }} PLN
                            <small class="text-sm">/{{ $t('orders.until_the_end_package') }}</small>
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