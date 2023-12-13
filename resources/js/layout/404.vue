<script>
    export default {
        props: {
            error: { type: String, default: "Obiekt nie istnieje" },
            back: { type: Object }
        },
        methods: {
            backClick() {
                this.$router.push(this.back)
            }
        }
    }
</script>
<template>
    <div class="grid">
        <div class="col col-12">
            <div class="pb-5 pt-5 mt-7">
                <div class="text-center text-xl font-medium">
                    {{ error }}
                </div>
                
                <div class="text-center mt-5">
                    <span class="pi pi-exclamation-circle" style="font-size: 6rem; color: var(--red-400)"></span>
                </div>
                
                <p class="text-center mt-5">
                    Wygląda na to, że wybrany element nie istnieje, bądź nie masz do niego dostępu.
                    <br/>
                    Wróć na poprzenią stronę i spróbuj pownownie.
                </p>
                
                <div class="mt-5 text-center">
                    <Button v-if="back" :label="$t('app.back')" @click="backClick" iconPos="left" icon="pi pi-angle-left"/>
                </div>
            </div>
        </div>
    </div>
</template>