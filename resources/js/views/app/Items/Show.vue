<script>
    import { setMetaTitle, hasAccess } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from '@/views/app/Items/_TabMenu.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    
    export default {
        components: { Rental, TabMenu },
        setup() {
            setMetaTitle('meta.title.items_show')
            return {
                hasAccess
            }
        },
        props: {
            item: { type: Object },
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                    items.push({'label' : this.item.name, disabled : true })
                    
                return items
            },
            
            rentItem() {
                this.$router.push({name: 'rent_source_item', params: { itemId : this.$route.params.itemId }})
            },
            
            showRental() {
                if (this.item.current_rental.id != undefined)
                    this.$router.push({name: 'rental_show', params: { rentalId : this.item.current_rental.id }})
            },
            
            archive() {
                this.$router.push({name: 'item_archive'})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" activeIndex="base" class="mb-5"/>
                
                <div class="grid mt-4">
                    <div class="col text-center" v-if="item.area">
                        <span class="font-medium">{{ $t('items.area') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.area, '0.00') }} (m2)</i>
                    </div>
                    <div class="col text-center" v-if="item.num_rooms">
                        <span class="font-medium">{{ $t('items.number_of_rooms') }}: </span>
                        <br/>
                        <i>{{ item.num_rooms }}</i>
                    </div>
                    <div class="col text-center" v-if="item.default_rent">
                        <span class="font-medium">{{ $t('items.default_rent_value') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.default_rent, '0.00') }} {{ item.currency }}</i>
                        
                    </div>
                    <div class="col text-center" v-if="item.default_deposit">
                        <span class="font-medium">{{ $t('items.default_deposit_value') }}: </span>
                        <br/>
                        <i>{{ numeralFormat(item.default_deposit, '0.00') }} {{ item.currency }}</i>
                    </div>
                    <div class="col text-center">
                        <span class="font-medium">{{ $t('items.ownership') }}: </span>
                        <br/>
                        <i>
                            <span v-if="item.ownership_type == 'manage'">
                                <router-link v-if="item.customer.id" :to="{name: 'customer_show', params: { customerId : item.customer.id }}">
                                    {{ item.customer.name }}
                                </router-link>
                            </span>
                            <span v-else>
                                {{ $t('items.own') }}
                            </span>
                        </i>
                    </div>
                </div>
                <p class="m-0 mt-3" v-if="item.comments">
                    <span class="font-medium">{{ $t('items.comments') }}: </span>
                    <br/>
                    <i class="text-sm">{{ item.comments}}</i>
                </p>
            </div>
            <div class="card" v-if="item.mode != 'archived'">
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                     <h4 class="inline-flex mb-0 mt-2 text-color font-medium">
                         {{ $t("items.currently_tenant") }}
                     </h4>
                </div>
                
                <div v-if="item.current_rental">
                    <div class="grid align-items-center">
                        <div class="col-12 lg:col-6">
                            <Rental :object="item.current_rental" />
                        </div>
                        <div class="col-12 lg:col-6 text-center" v-if="hasAccess('rent:list')">
                            <Button severity="success" :label="$t('rent.go_to_details')" @click="showRental" class="align-center mt-5" iconPos="right" icon="pi pi-external-link"></Button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="grid align-items-center">
                        <div class="col-12 lg:col-6 text-lg font-medium text-red-500">
                            {{ $t('items.currently_no_tenant') }}
                        </div>
                        <div class="col-12 lg:col-6 text-center" v-if="hasAccess('rent:create')">
                            <Button :label="$t('items.start_rental')" @click="rentItem" type="button" severity="danger" iconPos="right" icon="pi pi-briefcase" class="w-auto text-center" />
                        </div>
                    </div>
                </div>
            </div>
            
            <template v-if="item.mode != 'archived'">
                <div class="text-right">
                <template v-if="hasAccess('item:update') && item.can_archive">
                    <Button :label="$t('items.archive')" icon="pi pi-inbox" v-tooltip.top="$t('items.archive')" severity="contrast" class="p-2 ml-2" style="width: auto" @click="archive()"/>
                </template>
                </div>
            </template>
            
        </div>
    </div>
</template>