<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, timeToDate } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import RentalService from '@/service/RentalService'
    import TenantService from '@/service/TenantService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.tenants_show')
            
            const tenantService = new TenantService()
            const rentalService = new RentalService()
            
            return {
                tenantService,
                rentalService,
                hasAccess,
                getValueLabel,
                timeToDate
            }
        },
        data() {
            return {
                errors: [],
                tenant: {},
                rentals: [],
                meta: {
                    rentals: {
                        currentPage: 1,
                        perPage: this.rowsPerPage,
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
                    (response) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            editTenant() {
                this.$router.push({name: 'tenant_edit', params: { tenantId : this.$route.params.tenantId }})
            },
            
            rentItem() {
                this.$router.push({name: 'rent_source_tenant', params: { tenantId : this.$route.params.tenantId }})
            },
            
            getRentalsList() {
                const search = {
                    tenant_id : this.$route.params.tenantId
                };
                this.rentalService.list(this.meta.rentals.perPage, this.meta.rentals.currentPage, null, null, search)
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
                this.meta.items.currentPage = event["page"] + 1;
                this.getItemsList()
            },
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
                        {{ tenant.name }}
                        <div v-if="hasAccess('tenant:update')">
                            <Button icon="pi pi-pencil" @click="editTenant" v-tooltip.left="$t('app.edit')"></Button>
                        </div>
                    </div>
                </template>
                
                <template #content pt="item">
                    <div class="grid formgrid">
                        <div class="col-12 md:col-6">
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('tenants.account_type') }}: </span> <i>{{ getValueLabel('tenant_types', tenant.type) }}</i>
                            </p>
                            <p class="m-0 mt-2">
                                <span class="font-medium">{{ $t('tenants.address') }}: </span> <i><Address :object="tenant"/></i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="tenant.type == 'person'">
                                <span class="font-medium">{{ $t('tenants.pesel') }}: </span> <i v-if="tenant.pesel">{{ tenant.pesel }}</i><i v-else>-</i>
                            </p>
                            
                            <p class="m-0 mt-2" v-if="tenant.type == 'firm'">
                                <span class="font-medium">{{ $t('tenants.nip') }}: </span> <i v-if="tenant.nip">{{ tenant.nip }}</i><i v-else>-</i>
                            </p>
                        </div>
                        <div class="col-12 md:col-6">
                            <div class="font-medium mt-3">{{ $t('tenants.email_list') }}:</div>
                            <div v-if="tenant.contacts != undefined && tenant.contacts.email.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="email in tenant.contacts.email" class="mb-1">
                                        <a href="mailto:{{ email.val }}">{{ email.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                            
                            <div class="font-medium mt-2">{{ $t('tenants.phone_list') }}:</div>
                            <div v-if="tenant.contacts != undefined && tenant.contacts.phone.length">
                                <ul class="list-unstyled mt-2">
                                    <li v-for="phone in tenant.contacts.phone" class="mb-1">
                                        <a href="tel:{{ phone.val }}">{{ phone.val }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div v-else><i>-</i></div>
                        </div>
                    </div>
                    <p class="m-0 mt-2" v-if="tenant.comments">
                        <span class="font-medium">{{ $t('tenants.comments') }}: </span>
                        <br/>
                        <i class="text-sm">{{ tenant.comments}}</i>
                    </p>
                </template>
            </Card>
            
            
            <Card class="mb-3 mt-5">
                <template #title>
                    <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                        {{ $t('rent.rentals_list') }}
                        <div v-if="hasAccess('rent:create')">
                            <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('app.rent')"></Button>
                        </div>
                    </div>
                </template>
                <template #content pt="item">
                    <DataTable :value="rentals" stripedRows class="p-datatable-gridlines" :totalRecords="meta.rentals.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.rentals.totalPages" :rows="meta.rentals.perPage" @page="changeRentalsPage" :loading="meta.rentals.loading" @row-click="rowRentalsClick($event)">
                        <Column field="name" :header="$t('items.name')" style="min-width: 300px;">
                            <template #body="{ data }">
                                <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}">
                                        {{ data.item.name }}
                                    </router-link>
                                    
                                    <div>
                                        <small>
                                            <Address :object="data.item" :newline="true" emptyChar=""/>
                                        </small>
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
                                {{ timeToDate(data.start) }} - 
                                <span v-if="data.item.period == 'indeterminate'">{{ $t("rent.indeterminate") }}</span>
                                <span v-else>{{ timeToDate(data.end) }}</span>
                            </template>
                        </Column>
                        <Column :header="$t('rent.rent')">
                            <template #body="{ data }">
                                {{ numeralFormat(data.rent, '0.00') }}
                            </template>
                        </Column>
                        
                        <template #empty>
                            {{ $t('rent.empty_list') }}
                        </template>
                    </DataTable>
                </template>
            </Card>
            
        </div>
        
        
        <div class="col col-12">
            <div class="grid mt-1">
                <div class="col col-12">
                    <Card class="mb-3">
                        <template #title>
                            <div class="flex justify-content-between align-items-center mb-3 text-color font-medium">
                                Najemca
                                <div v-if="hasAccess('rent:create')">
                                    <Button icon="pi pi-plus" @click="rentItem" v-tooltip.left="$t('items.add_new_tenant')"></Button>
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