<script>
    import { useRoute, useRouter } from 'vue-router'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.items_show')
            
            const route = useRoute()
            const router = useRouter()
            const itemService = new ItemService()
            
            return {
                itemService,
                route,
                router,
                hasAccess
            }
        },
        data() {
            return {
                errors: [],
                item: {
                    customer: {}
                },
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.$t('items.edit'), disabled : true },
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
            
            this.itemService.get(this.route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            editItem() {
                this.router.push({name: 'item_edit', params: { itemId : this.route.params.itemId }})
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
                        {{ item.name }}
                        <div v-if="hasAccess('item:update')">
                            <Button icon="pi pi-pencil" @click="editItem" v-tooltip.left="$t('app.edit')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    <p class="m-0">
                        <span class="font-medium">{{ $t('items.estate_type') }}: </span> <i>{{ item.type }}</i>
                    </p>
                    <p class="m-0 mt-2">
                        <span class="font-medium">{{ $t('items.address') }}: </span> <i><Address :object="item"/></i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.area">
                        <span class="font-medium">{{ $t('items.area') }}: </span> <i>{{ numeralFormat(item.area, '0.00') }} (m2)</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.number_of_rooms">
                        <span class="font-medium">{{ $t('items.number_of_rooms') }}: </span> <i>{{ item.number_of_rooms }}</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.default_rent">
                        <span class="font-medium">{{ $t('items.default_rent_value') }}: </span> <i>{{ numeralFormat(item.default_rent, '0.00') }}</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.default_deposit">
                        <span class="font-medium">{{ $t('items.default_deposit_value') }}: </span> <i>{{ numeralFormat(item.default_deposit, '0.00') }}</i>
                    </p>
                    <p class="m-0 mt-2">
                        <span class="font-medium">{{ $t('items.ownership') }}: </span>
                        <i>
                            <span v-if="item.ownership">{{ $t('items.own') }}</span>
                            <span v-else>
                                <router-link v-if="item.customer.id" :to="{name: 'customer_show', params: { customerId : item.customer.id }}">
                                    {{ item.customer.name }}
                                </router-link>
                            </span>
                        </i>
                    </p>
                </template>
            </Card>
        </div>
        <div class="col col-12">
            <div class="grid mt-1">
                <div class="col col-12 sm:col-6">
                    <Card class="mb-3">
                        <template #title>
                            <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                                Najemca
                                <div v-if="hasAccess('item:update')">
                                    <Button icon="pi pi-plus" @click="editItem" v-tooltip.left="$t('items.add_new_tenant')"></Button>
                                </div>
                            </div>
                        </template>
                        <template #content pt="item">
                            <p>Lorem ipsum dolor sit amet</p>
                        </template>
                    </Card>
                </div>
                
                <div class="col col-12 sm:col-6">
                    <Card class="mb-3">
                        <template #title>
                            <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                                Rezerwacja
                                <div v-if="hasAccess('item:update')">
                                    <Button icon="pi pi-plus" @click="editItem" v-tooltip.left="$t('items.add_new_reservation')"></Button>
                                </div>
                            </div>
                        </template>
                        <template #content pt="item">
                            <p>Lorem ipsum dolor sit amet</p>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </div>
    
</template>