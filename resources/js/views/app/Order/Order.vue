<script>
    import { getPrices, getResponseErrors, setMetaTitle, getLocale } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required, minValue, maxValue } from '@/utils/i18n-validators'
    import Slider from 'primevue/slider';
    import OrderService from '@/service/OrderService'
    
    export default {
        components: { Slider },
        setup() {
            setMetaTitle('meta.title.buy_access')
            
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
                minItems: 1,
                maxItems: 1000,
                saving: false,
                disabled: true,
                order: {
                    total: 10,
                    period: "p1",
                },
                locale: getLocale(),
                prices: getPrices(),
                showForm: false,
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
                        if(response.data.start != undefined)
                            this.$router.push({name: 'order_prolong'})
                        else
                            this.showForm = true
                    },
                    (errors) => {},
                )
        },
        methods: {
            async submitForm() {
                const result = await this.v.$validate()
                if (result && this.invoicingOk) {
                    this.saving = true;
                    this.orderService.order(this.order)
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
                return this.order.total * this.prices[this.order.period].price_vat
            }
        },
        validations () {
            return {
                order: {
                    total: { required, minValue: minValue(1), maxValueValue: maxValue(1000) },
                    period: { required },
                }
            }
        },
    }
</script>
<template>
    <div class="card p-fluid mt-4" v-if="showForm">
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('orders.buy_access') }}</h4>
        
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
                    {{ $t('orders.order_info_line_1' )}}
                </div>
                
                <div class="col-12 sm:col-8">
                    <div class="mb-6">
                        {{ $t('orders.select_period') }}:
                        <ul class="list-unstyled">
                            <li v-for="(price, key) in prices" class="pt-1 pb-1 flex align-items-center">
                                <RadioButton v-model="order.period" name="period" :inputId="price.name" :value="key" :disabled="disabled" />
                                <label :for="price.name" class="ml-2">
                                    <span class="font-medium">{{ price[locale].name }}</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                    
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
                <div class="col-12 sm:col-4">
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.period') }}:</span>
                        <div class="text-xl">
                            {{ prices[order.period][locale].name }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.selected_items') }}:</span>
                        <div class="text-xl">
                            {{ order.total }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-sm">{{ $t('orders.price_per_item') }}:</span>
                        <div class="text-xl">
                            {{ numeralFormat(prices[order.period].price_vat, '0.00') }} PLN
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