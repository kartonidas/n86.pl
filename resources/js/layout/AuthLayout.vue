<script setup>
</script>

<template>
    <div class="signin-page">
        <div class="grid m-0">
            <div class="col-12 lg:col-5 xl:col-4 col-wrapper bg-white">
                <div class="w-full py-6 px-5 sm:px-8">
                    <div>
                        <div class="text-center mb-5">
                            <img src="/images/logo.svg" style="max-height: 50px">
                            <div class="mt-2" style="font-weight: 500">
                                {{ $t('app.register_title') }}
                            </div>
                        </div>
                        <router-view></router-view>
                    </div>
                </div>
            </div>
            <div class="col hidden lg:block auth-background">
                
            </div>
        </div>
    </div>
</template>