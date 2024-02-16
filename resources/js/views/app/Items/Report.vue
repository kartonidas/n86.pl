<script>
    import { setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Chart from 'primevue/chart';
    import TabMenu from '@/views/app/Items/_TabMenu.vue'
    import ReportService from '@/service/ReportService'
    import Skeleton from 'primevue/skeleton';
    import moment from 'moment'
    
    export default {
        components: { TabMenu, Chart, Skeleton },
        props: {
            item: { type: Object },
        },
        watch: {
            "chart.profit.years" : function() {
                this.setPeriod()
            },
            "chart.balance.years" : function() {
                this.setPeriod()
            }
        },

        setup() {
            setMetaTitle('meta.title.items_report')
            const reportService = new ReportService()
            return {
                reportService,
            }
        },
        data() {
            return {
                summary: {
                    loading: true,
                    data: {
                        charge: 0,
                        deposit: 0,
                        cost: 0,
                        balance: 0,
                        profit: 0,
                    }
                },
                chart: {
                    balance: {
                        loading: true,
                        period: moment().year(),
                        summary: {
                            charge: 0,
                            deposit: 0,
                            balance: 0
                        },
                        years: [],
                        allowedPeriods: [],
                        data: {},
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    }
                                },
                                y: {
                                    grace: '5%'
                                }
                            }
                        },
                    },
                    profit: {
                        loading: true,
                        period: moment().year(),
                        summary: {
                            profit: 0,
                            plus: 0,
                            minus: 0
                        },
                        years: [],
                        allowedPeriods: [],
                        data: {},
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    }
                                },
                                y: {
                                    grace: '5%'
                                }
                            }
                        }
                    },
                },
            }
        },
        beforeMount() {
            this.setPeriod()
            this.getBalanceReport()
            this.getProfitReport()
            this.getItemReport()
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.history'), disabled : true })
                }
                    
                return items
            },
            
            setPeriod() {
                for (const [key, data] of Object.entries(this.chart)) {
                    let period = [
                        { "id" : "last_year", "name" : this.$t("items.last_12_months") }
                    ];
                    
                    if (!data.years.length) {
                        period.push({ "id" : moment().year(), "name" : moment().year() });
                    }
                    else
                    {
                        data.years.forEach((year) => {
                            period.push({ "id" : year, "name" : year });
                        });
                    }
                    this.chart[key].allowedPeriods = period
                }
            },
            
            changeBalancePeriod(e) {
                this.chart.balance.period = e.value
                this.getBalanceReport()
            },
            
            changeProfitPeriod(e) {
                this.chart.profit.period = e.value
                this.getProfitReport()
            },
            
            getBalanceReport() {
                this.chart.balance.loading = true
                this.reportService.itemBalanceReport(this.$route.params.itemId, this.chart.balance.period)
                    .then(
                        (response) => {
                            let labels = response.data.labels;
                            
                            this.chart.balance.data.datasets = [
                                {
                                    label: response.data.charge.name,
                                    data: response.data.charge.values,
                                    backgroundColor: '#fcb3b3',
                                },
                                {
                                    label: response.data.deposit.name,
                                    data: response.data.deposit.values,
                                    backgroundColor: '#79c985',
                                },
                                {
                                    label: response.data.balance.name,
                                    data: response.data.balance.values,
                                    backgroundColor: '#3282ba',
                                },
                            ]

                            this.chart.balance.data.labels = labels
                            this.chart.balance.years = response.data.years
                            this.chart.balance.summary = response.data.summary
                            this.chart.balance.summary.title = response.data.title
                            this.chart.balance.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            getProfitReport() {
                this.chart.profit.loading = true
                this.reportService.itemProfitReport(this.$route.params.itemId, this.chart.profit.period)
                    .then(
                        (response) => {
                            let labels = response.data.labels;
                            this.chart.profit.data.datasets = [
                                {
                                    label: response.data.rent_paid.name,
                                    data: response.data.rent_paid.values,
                                    backgroundColor: '#79c985',
                                    stack: 'Stack 0'
                                },
                                {
                                    label: response.data.rent_unpaid.name,
                                    data: response.data.rent_unpaid.values,
                                    backgroundColor: '#d2f7d8',
                                    stack: 'Stack 0'
                                },
                                {
                                    label: response.data.fee_paid.name,
                                    data: response.data.fee_paid.values,
                                    backgroundColor: '#fcb3b3',
                                    stack: 'Stack 1'
                                },
                                {
                                    label: response.data.fee_unpaid.name,
                                    data: response.data.fee_unpaid.values,
                                    backgroundColor: '#ffe3e3',
                                    stack: 'Stack 1'
                                },
                                {
                                    label: response.data.profit.name,
                                    data: response.data.profit.values,
                                    backgroundColor: '#3282ba',
                                    type: 'line',
                                    cubicInterpolationMode: 'monotone',
                                    fill: false,
                                },
                                {
                                    label: response.data.expected_profit.name,
                                    data: response.data.expected_profit.values,
                                    backgroundColor: '#3282ba',
                                    type: 'line',
                                    cubicInterpolationMode: 'monotone',
                                    fill: false,
                                },
                            ]
                            
                            this.chart.profit.data.labels = labels
                            this.chart.profit.years = response.data.years
                            this.chart.profit.summary = response.data.summary
                            this.chart.profit.summary.title = response.data.title
                            this.chart.profit.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
            
            getItemReport() {
                this.summary.loading = true
                this.reportService.itemReport(this.$route.params.itemId)
                    .then(
                        (response) => {
                            this.summary.loading = false
                            this.summary.data = response.data;
                        },
                        (errors) => {
                        }
                    )
                
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" :showEditButton="false" activeIndex="report" class="mb-5"/>
                
                <div class="grid">
                    <div class="col-12 sm:col" style="min-height: 135px">
                        <Skeleton class="h-full shadow-4" v-if="summary.loading"></Skeleton>
                        <div class="shadow-4 px-3 py-5 text-center relative" v-if="!summary.loading">
                            <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_total_rent_amount')"></span>
                            <div class="text-sm font-medium uppercase">
                                {{ $t('reports.total_rent_amount') }}:
                            </div>
                            <div class="text-2xl mt-2">
                                {{ numeralFormat(summary.data.total_rent, '0.00') }}
                                <div class="text-sm mt-2">
                                    {{ this.summary.data.currency }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 sm:col" style="min-height: 135px">
                        <Skeleton class="h-full shadow-4" v-if="summary.loading"></Skeleton>
                        <div class="shadow-4 px-3 py-5 text-center relative" v-if="!summary.loading">
                            <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_total_fee_amount')"></span>
                            <div class="text-sm font-medium uppercase">
                                {{ $t('reports.total_fee_amount') }}:
                            </div>
                            <div class="text-2xl mt-2">
                                {{ numeralFormat(summary.data.total_fees, '0.00') }}
                                <div class="text-sm mt-2">
                                    {{ this.summary.data.currency }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 sm:col" style="min-height: 135px">
                        <Skeleton class="h-full shadow-4" v-if="summary.loading"></Skeleton>
                        <div class="shadow-4 px-3 py-5 text-center relative" v-if="!summary.loading">
                            <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_total_profit_amount')"></span>
                            <div class="text-sm font-medium uppercase">
                                {{ $t('reports.total_profit_amount') }}:
                            </div>
                            <div class="text-2xl mt-2">
                                {{ numeralFormat(summary.data.profit, '0.00') }}
                                <div class="text-sm mt-2">
                                    {{ this.summary.data.currency }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                                      
                <TabView class="mt-5">
                    <TabPanel :header="$t('items.report_cost')">
                        <div class="text-right">
                            <Dropdown v-model="chart.balance.period" :showClear="false" :options="chart.balance.allowedPeriods" optionLabel="name" optionValue="id" :placeholder="$t('items.select_period')" @change="changeBalancePeriod" :loading="chart.balance.loading"/>
                        </div>
                        
                        <div class="mb-5">
                            <div class="text-xl mb-3">
                                {{ chart.profit.summary.title }}
                            </div>
                            <div class="grid">
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_balance_charge')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_balance_charge') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.balance.summary.charge, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_balance_deposit')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_balance_deposit') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.balance.summary.deposit, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_balance_balance')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_balance_balance') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.balance.summary.balance, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <Chart type="bar" :data="chart.balance.data" :options="chart.balance.options" class="h-30rem"/>
                    </TabPanel>
                    <TabPanel :header="$t('items.report_profit')">
                        <div class="text-right">
                            <Dropdown v-model="chart.profit.period" :showClear="false" :options="chart.profit.allowedPeriods" optionLabel="name" optionValue="id" :placeholder="$t('items.select_period')" @change="changeProfitPeriod" :loading="chart.balance.loading"/>
                        </div>
                        
                        <div class="mb-5">
                            <div class="text-xl mb-3">
                                {{ chart.profit.summary.title }}
                            </div>
                            <div class="grid">
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_profit_charge')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_profit_deposit') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.profit.summary.plus, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_profit_fee')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_profit_cost') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.profit.summary.minus, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 sm:col-4" style="min-height: 135px">
                                    <Skeleton class="h-full shadow-4" v-if="chart.profit.loading"></Skeleton>
                                    <div class="shadow-4 px-3 py-5 text-center relative" v-if="!chart.profit.loading">
                                        <span class="pi pi-question-circle absolute" style="top: 10px; right: 10px" v-tooltip.top="$t('reports.help_report_profit_balance')"></span>
                                        <div class="text-sm font-medium uppercase">
                                            {{ $t('items.report_profit_profit') }}:
                                        </div>
                                        <div class="text-3xl mt-1">
                                            {{ numeralFormat(chart.profit.summary.profit, '0.00') }}
                                            {{ chart.profit.summary.currency }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <Chart type="bar" :data="chart.profit.data" :options="chart.profit.options" class="h-30rem"/>
                    </TabPanel>
                </TabView>
            </div>
        </div>
    </div>
</template>