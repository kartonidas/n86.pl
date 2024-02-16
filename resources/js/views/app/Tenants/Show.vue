<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Header from '@/views/app/_partials/Header.vue'
    import RentalService from '@/service/RentalService'
    import TenantService from '@/service/TenantService'
    
    export default {
        components: { Address, Header },
        setup() {
            setMetaTitle('meta.title.tenants_show')
            
            const tenantService = new TenantService()
            const rentalService = new RentalService()
            
            return {
                tenantService,
                rentalService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                errors: [],
                tenant: {},
                rentals: [],
                meta: {
                    rentals: {
                        list: {
                            first: 0,
                            size: this.rowsPerPage,
                        },
                        totalRecords: null,
                        totalPages: null,
                        loading: true
                    },
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.tenant_list'), route : { name : 'tenants'} },
                        {'label' : this.$t('tenants.card'), disabled : true },
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
            
            this.tenantService.get(this.$route.params.tenantId)
                .then(
                    (response) => {
                        this.tenant = response.data
                        this.loading = false
                        
                        this.getRentalsList()
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
            rentItem() {
                this.$router.push({name: 'rent_source_tenant', params: { tenantId : this.$route.params.tenantId }})
            },
            
            getRentalsList() {
                const search = {
                    tenant_id : this.$route.params.tenantId
                };
                this.rentalService.list(this.meta.rentals.list, search)
                    .then(
                        (response) => {
                            this.rentals = response.data.data
                            this.meta.rentals.totalRecords = response.data.total_rows
                            this.meta.rentals.totalPages = response.data.total_pages
                            this.meta.rentals.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            changeRentalsPage(event) {
                this.meta.rentals.list.first = event["first"];
                this.getRentalsList()
            },
            
            rowRentalsClick(event) {
                this.$router.push({name: 'rental_show', params: { rentalId : event.data.id }})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
             <div class="card mb-3">
                <Header :object="tenant" type="tenant" :showEditButton="true"/>
                
                <div class="mt-3">
                    <p class="m-0 mt-2" v-if="tenant.type == 'person'">
                        <span class="font-medium">{{ $t('tenants.pesel') }}: </span> <i v-if="tenant.pesel">{{ tenant.pesel }}</i><i v-else>-</i>
                    </p>
                    
                    <p class="m-0 mt-2" v-if="tenant.type == 'firm'">
                        <span class="font-medium">{{ $t('tenants.nip') }}: </span> <i v-if="tenant.nip">{{ tenant.nip }}</i><i v-else>-</i>
                    </p>
                </div>
                
                <div class="grid mt-3">
                    <div class="col-12 md:col-6">
                        <div class="font-normal mb-2 text-lg">{{ $t('tenants.phone_list') }}:</div>
                        <div v-if="tenant.contacts != undefined && tenant.contacts.phone.length">
                            <ul class="list-unstyled mt-2">
                                <li v-for="phone in tenant.contacts.phone" class="mb-1">
                                    <a href="tel:{{ phone.val }}">{{ phone.val }}</a>
                                </li>
                            </ul>
                        </div>
                        <div v-else><i>{{ $t('tenants.empty_phone_list') }}</i></div>
                    </div>
                    <div class="col-12 md:col-6">
                        <div class="font-normal mb-2 text-lg">{{ $t('tenants.email_list') }}:</div>
                        <div v-if="tenant.contacts != undefined && tenant.contacts.email.length">
                            <ul class="list-unstyled mt-2">
                                <li v-for="email in tenant.contacts.email" class="mb-1">
                                    <a href="mailto:{{ email.val }}">{{ email.val }}</a>
                                </li>
                            </ul>
                        </div>
                        <div v-else><i>{{ $t('tenants.empty_email_list') }}</i></div>
                    </div>
                </div>
                
                    <p class="m-0 mt-2" v-if="tenant.comments">
                        <span class="font-medium">{{ $t('tenants.comments') }}: </span>
                        <br/>
                        <i class="text-sm">{{ tenant.comments}}</i>
                    </p>
            </div>
            
            
            <div class="card mt-5">
                <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                    <h5 class="inline-flex mb-0 mt-2 text-color font-medium">
                        {{ $t('rent.rentals_list') }}
                    </h5>
                    <div v-if="hasAccess('rent:create')">
                        <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('app.rent')"></Button>
                    </div>
                </div>
                <DataTable :value="rentals" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.rentals.totalPages" :rows="meta.rentals.list.size" :first="meta.rentals.list.first" @page="changeRentalsPage" :loading="meta.rentals.loading" @row-click="rowRentalsClick($event)">
                    <Column field="name" :header="$t('items.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <div :class="data.item.mode == 'archived' ? 'archived-item' : ''">
                                <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    <i class="pi pi-lock pr-1" v-if="data.item.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
                                    <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}">
                                        {{ data.item.name }}
                                    </router-link>
                                    
                                    <div>
                                        <small>
                                            <Address :object="data.item" :newline="true" emptyChar=""/>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column :header="$t('rent.status')">
                        <template #body="{ data }">
                            {{ getValueLabel('rental.statuses', data.status) }}
                        </template>
                    </Column>
                    <Column :header="$t('rent.period_short')">
                        <template #body="{ data }">
                            {{ data.start }} - 
                            <span v-if="data.item.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                            <span v-else>{{ data.end }}</span>
                        </template>
                    </Column>
                    <Column :header="$t('rent.rent')">
                        <template #body="{ data }">
                            {{ numeralFormat(data.rent, '0.00') }}
                            {{ data.currency }}
                        </template>
                    </Column>
                    
                    <template #empty>
                        {{ $t('rent.empty_list') }}
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>