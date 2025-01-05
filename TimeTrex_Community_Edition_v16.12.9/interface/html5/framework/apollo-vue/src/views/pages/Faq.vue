<script setup>
import { ref } from 'vue';
import { usePrimeVue } from 'primevue/config';
const $primevue = usePrimeVue();

defineExpose({
    $primevue
});

const activeIndex = ref(0);
const items = ref([
    {
        label: 'General',
        icon: 'pi pi-fw pi-info-circle',
        questions: ['Is there a trial period?', 'Do I need to sign up with credit card?', 'Is the subscription monthly or annual?', 'How many tiers are there?']
    },
    {
        label: 'Mailing',
        icon: 'pi pi-fw pi-envelope',
        questions: ['How do I setup my account?', 'Is there a limit on mails to send?', 'What is my inbox size?', 'How can I add attachements?']
    },
    {
        label: 'Support',
        icon: 'pi pi-fw pi-question-circle',
        questions: ['How can I get support?', 'What is the response time?', 'Is there a community forum?', 'Is live chat available?']
    },
    {
        label: 'Billing',
        icon: 'pi pi-fw pi-credit-card',
        questions: ['Will I receive an invoice?', 'How to provide my billing information?', 'Is VAT included?', 'Can I receive PDF invoices?']
    }
]);

const changeItem = (index) => {
    activeIndex.value = index;
};
</script>

<template>
    <div>
        <div class="card">
            <div class="text-900 font-bold text-xl mb-3">Frequently Asked Questions</div>
            <p class="text-600 line-height-3">Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.</p>
        </div>

        <div class="flex flex-column md:flex-row gap-5">
            <div class="card mb-0 md:w-20rem">
                <div class="text-900 block font-bold mb-3">Categories</div>
                <ul class="list-none m-0 p-0">
                    <li v-for="(item, i) in items" :key="i" @click="changeItem(i)" class="mb-2">
                        <a
                            v-ripple
                            class="p-ripple flex align-items-center cursor-pointer select-none p-3 transition-colors transition-duration-150 border-round"
                            :class="{
                                'bg-primary': activeIndex === i,
                                'hover:surface-hover': activeIndex !== i
                            }"
                        >
                            <i class="mr-2 text-lg" :class="item.icon"></i>
                            <span>{{ item.label }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card flex-1">
                <Accordion>
                    <AccordionTab v-for="(question, i) in items[activeIndex].questions" :key="i" :header="question">
                        <p class="line-height-3 m-0 p-0">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
                            laborum.
                        </p>
                    </AccordionTab>
                </Accordion>
            </div>
        </div>
    </div>
</template>
