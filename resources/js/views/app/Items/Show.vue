<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute, useRouter } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { getResponseErrors, hasAccess } from '@/utils/helper'
    
    import ItemService from '@/service/ItemService'
    
    export default {
        setup() {
            const route = useRoute()
            const router = useRouter()
            const itemService = new ItemService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                itemService,
                route,
                router,
                toast,
                hasAccess
            }
        },
        data() {
            return {
                errors: [],
                item: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.estates'), disabled : true },
                        {'label' : this.t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.t('items.edit'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            this.itemService.get(this.route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
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
                    <div class="flex justify-content-between align-items-center mb-3">
                        {{ item.name }}
                        <div v-if="hasAccess('item:update')">
                            <Button icon="pi pi-pencil" @click="editItem" v-tooltip.left="$t('app.edit')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    <p class="m-0">
                        <strong>{{ $t('items.estate_type') }}: </strong> <i>{{ item.type }}</i>
                    </p>
                    <p class="m-0 mt-2">
                        <strong>{{ $t('items.address') }}: </strong> <i>{{ item.street }}<span v-if="item.house_no">&nbsp;{{ item.house_no }}</span><span v-if="item.apartment_no"><span v-if="item.house_no">/</span><span v-else>&nbsp;</span>{{ item.apartment_no }}</span>, {{ item.zip }} {{ item.city }}</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.area">
                        <strong>{{ $t('items.area') }}: </strong> <i>{{ item.area }} (m2)</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.number_of_rooms">
                        <strong>{{ $t('items.number_of_rooms') }}: </strong> <i>{{ item.number_of_rooms }}</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.default_rent">
                        <strong>{{ $t('items.default_rent_value') }}: </strong> <i>{{ item.default_rent }}</i>
                    </p>
                    <p class="m-0 mt-2" v-if="item.default_deposit">
                        <strong>{{ $t('items.default_deposit_value') }}: </strong> <i>{{ item.default_deposit }}</i>
                    </p>
                    <p class="m-0 mt-2">
                        <strong>Własność: </strong>
                        <i>
                            <span v-if="item.ownership">Moja</span>
                            <span v-else>Nie moja</span>
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
                            <div class="flex justify-content-between align-items-center mb-3">
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
                            <div class="flex justify-content-between align-items-center mb-3">
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