<script>
    import { useRoute, useRouter } from 'vue-router'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import CustomerService from '@/service/CustomerService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.customers_show')
            
            const route = useRoute()
            const router = useRouter()
            const customerService = new CustomerService()
            
            return {
                customerService,
                route,
                router,
                hasAccess
            }
        },
        data() {
            return {
                errors: [],
                customer: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.$t('customers.card'), disabled : true },
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
            
            this.customerService.get(this.route.params.customerId)
                .then(
                    (response) => {
                        this.customer = response.data
                        this.customer.type_label = this.customerService.getTypeLabel(this.$t, this.customer.type)
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            editCustomer() {
                this.router.push({name: 'customer_edit', params: { customerId : this.route.params.customerId }})
            },
            
            addItem() {
                console.log("Redirect to item create form")
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
             <Card class="mb-3">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ customer.name }}
                        <div v-if="hasAccess('customer:update')">
                            <Button icon="pi pi-pencil" @click="editCustomer" v-tooltip.left="$t('app.edit')"></Button>
                        </div>
                    </div>
                </template>
                
                <template #content pt="item">
                    <div class="grid formgrid">
                        <div class="col-12 md:col-6">
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('customers.account_type') }}: </span> <i>{{ customer.type_label }}</i>
                            </p>
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('customers.address') }}: </span> <i><Address :object="customer"/></i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="customer.type == 'person'">
                                <span class="font-medium">{{ $t('customers.pesel') }}: </span> <i v-if="customer.pesel">{{ customer.pesel }}</i><i v-else>-</i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="customer.type == 'firm'">
                                <span class="font-medium">{{ $t('customers.nip') }}: </span> <i v-if="customer.nip">{{ customer.nip }}</i><i v-else>-</i>
                            </p>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="font-medium mt-3">{{ $t('customers.email_list') }}:</div>
                            <div v-if="customer.contacts != undefined && customer.contacts.email.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="email in customer.contacts.email" class="mb-1">
                                        <a href="mailto:{{ email.val }}">{{ email.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                            
                            <div class="font-medium mt-2">{{ $t('customers.phone_list') }}:</div>
                            <div v-if="customer.contacts != undefined && customer.contacts.phone.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="phone in customer.contacts.phone" class="mb-1">
                                        <a href="tel:{{ phone.val }}">{{ phone.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                        </div>
                    </div>
                    <p class="m-0 mt-2" v-if="customer.comments">
                        <span class="font-medium">{{ $t('customers.comments') }}: </span>
                        <br/>
                        <i class="text-sm">{{ customer.comments}}</i>
                    </p>
                </template>
            </Card>
            
            <Card class="mb-3 mt-5">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ $t('customers.items_list') }}
                        <div v-if="hasAccess('item:create')">
                            <Button icon="pi pi-plus" @click="addItem" v-tooltip.left="$t('customers.add_new_item')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    
                </template>
            </Card>
        </div>
    </div>
</template>