<script>
    import { ref } from 'vue'
    import { setMetaTitle, getValueLabel } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    import Package from './_partials/Package.vue';
    import DashboardService from '@/service/DashboardService'
    import Skeleton from 'primevue/skeleton';
    
    export default {
        components: { Address, Package, Skeleton },
        setup() {
            setMetaTitle('meta.title.dashboard')
            
            const dashboardService = new DashboardService()
            
            const dashboard = ref({
                total_items : 0,
                    total_rentals : 0,
            })
            
            return {
                dashboardService,
                dashboard,
                getValueLabel
            }
        },
        beforeMount() {
            this.dashboardService.get()
                .then(
                    (response) => {
                        this.dashboard = response.data
                        this.loading = false
                    },
                    (errors) => {},
                )
        },
        data() {
            return {
                loading: true
            }
        },
        methods: {
            rowBillClick(e) {
                this.$router.push({name: 'item_bill_show', params: {itemId: e.data.item.id, billId: e.data.id}})
            },
            
            rowRentalClick(e) {
                this.$router.push({name: 'rental_show', params: {rentalId: e.data.id}})
            }
        }
    }
</script>

<template>
    
    <div class="grid">
        <div class="col-12 sm:col-4 xl:col-4">
            <Skeleton class="h-full" v-if="loading"></Skeleton>
            <div class="bg-gray-200 p-3 text-center text-sm border-round-lg h-full flex flex-column justify-content-center" v-if="!loading">
                <div class="text-sm uppercase">{{ $t("dashboard.total_items") }}</div>
                <div class="text-5xl mt-1">{{ dashboard.total_items }}</div>
            </div>
        </div>
        <div class="col-12 sm:col-4 xl:col-4">
            <Skeleton class="h-full" v-if="loading"></Skeleton>
            <div class="bg-gray-200 p-3 text-center text-sm border-round-lg h-full flex flex-column justify-content-center" v-if="!loading">
                <div class="text-sm uppercase">{{ $t("dashboard.total_active_rentals") }}:</div>
                <div class="text-5xl mt-1">{{ dashboard.total_rentals }}</div>
            </div>
        </div>
            
        <div class="col-12 sm:col-4 xl:col-4">
            <Package class="h-full flex flex-column justify-content-center p-4"/>
        </div>
        
        <div class="col-12">
            <div class="card">
                <div class="grid">
                    <div class="col-12 xl:col-6">
                        <div class="mb-3 font-medium">
                            {{ $t("dashboard.unpaid_bills") }}
                        </div>
                        <DataTable size="small" :value="dashboard.unpaid_bills" stripedRows class="p-datatable-gridlines clickable" :lazy="true" :loading="loading" @row-click="rowBillClick($event)">
                            <Column :header="$t('items.bill_type')" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <div class="mb-1 text-base font-medium">
                                         <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}">
                                            {{ data.item.name }}
                                        </router-link>
                                    </div>
                                    <div class="mb-1 text-sm">
                                        {{ data.bill_type.name }}, {{ data.payment_date }}
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('items.cost')" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.cost, '0.00') }}
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('dashboard.empty_unpaid_bills_list') }}
                            </template>
                        </DataTable>
                    </div>
                    
                    <div class="col-12 xl:col-6">
                        <div class="mb-3 font-medium">
                            {{ $t("dashboard.upcoming_bills") }}
                        </div>
                        <DataTable size="small" :value="dashboard.upcoming_bills" stripedRows class="p-datatable-gridlines clickable" :lazy="true" :loading="loading" @row-click="rowBillClick($event)">
                            <Column :header="$t('items.bill_type')" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <div class="mb-1 text-base font-medium">
                                        <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}">
                                            {{ data.item.name }}
                                        </router-link>
                                    </div>
                                    <div class="mb-1 text-sm">
                                        {{ data.bill_type.name }}, {{ data.payment_date }}
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('items.cost')" class="text-right">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.cost, '0.00') }}
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('dashboard.empty_upcoming_bills_list') }}
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card">
                <div class="grid">
                    <div class="col-12">
                        <div class="mb-3 font-medium">
                            {{ $t("dashboard.ending_rentals") }}
                        </div>
                        <DataTable :value="dashboard.rentals" size="small" stripedRows class="p-datatable-gridlines clickable" :lazy="true" :loading="loading" @row-click="rowRentalClick($event)">
                            <Column :header="$t('rent.number')" field="full_number">
                                <template #body="{ data }">
                                    <router-link :to="{name: 'rental_show', params: { rentalId : data.id }}">
                                        {{ data.full_number }}
                                    </router-link>
                                    <div>
                                        <small>{{ data.document_date }}</small>
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('rent.balance')" field="balance">
                                <template #body="{ data }">
                                    {{ numeralFormat(data.balance, '0.00') }}
                                </template>
                            </Column>
                            <Column :header="$t('rent.estate')" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <Badge :value="getValueLabel('item_types', data.item.type)" class="font-normal" severity="info"></Badge>
                                    <div class="mt-1">
                                        {{ data.item.name }}
                                        
                                        <div>
                                            <small>
                                                <Address :object="data.item" :newline="true" emptyChar=""/>
                                            </small>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('rent.tenant')" style="min-width: 300px;">
                                <template #body="{ data }">
                                    <Badge :value="getValueLabel('tenant_types', data.tenant.type)" class="font-normal" severity="info"></Badge>
                                    <div class="mt-1">
                                        {{ data.tenant.name }}
                                        
                                        <div>
                                            <small>
                                                <Address :object="data.tenant" :newline="true" emptyChar=""/>
                                            </small>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <template #empty>
                                {{ $t('dashboard.empty_ending_rentals_list') }}
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>