// #VUETEST

import { defineAsyncComponent } from 'vue'
// import InputText from 'primevue/inputtext';
export default {
	template: `
		<p><strong>This is an example of a fully JS defined component, with no build steps, all processed in the browser.</strong></p>
		<InputText type="text" v-model="reactive_1" /> {{ reactive_1 }}
		<div class="test">Hello World</div>
        <p>&nbsp;</p>
		<hr>
		<div class="edit-field-container">
			<div class="edit-field" v-for="field in edit_fields">
				<span style="display: inline-block; width:110px" class="label">{{ field.label }}: </span><span style="display: inline-block; width:200px" class="field"><component :is="field.type" v-model="field.value" v-bind="field.data" /></span>
			</div>
			<Button @click="addField" label="Add Dynamic field" />
		</div>
		<hr>
		<p>&nbsp;</p>
		<div>Show reactive data from manual input: {{ reactive_1 }}</div>
        <div>Show reactive data from dynamic form firstname: {{ edit_fields.filter((item)=> item.id === 'first_name')[0].value }}</div>
        <div>Show reactive data from dynamic form selection dropdown: {{ edit_fields.filter((item)=> item.id === 'dropdown1')[0].value }}</div>
	`,
	data() {
		return {
			reactive_1: 'hello',
			edit_fields: [
				{
					type: 'Button',
					label: 'My Button',
					data: {
							label: 'I am a button'
						}
				},
				{
					type: 'InputText',
					id: 'first_name',
					label: 'First name',
					value: 'default',
				},
				{
					type: 'InputText',
					label: 'Last name',
					value: '',
				},
				{
					type: 'InputText',
					label: 'Department',
					value: '',
				},
				{
					type: 'InputText',
					label: 'Something else',
					value: '',
				},
				{
					type: 'Dropdown',
					id: 'dropdown1',
					label: 'Selection',
					value: null,
					data: {
						placeholder: 'Select option',
						optionLabel: 'label',
						options: [
							{label: 'New York', value: 'NY'},
							{label: 'Rome', value: 'RM'},
							{label: 'London', value: 'LDN'},
						]
					}
				},
				{
					type: 'InputText',
					label: 'More names',
					value: 'default',
				},
			],
		}
	},
	mounted() {
		window.EditView = this;
		// this.componentToDisplay = Global.component;// + '.vue'
	},
	computed: {

	},
	methods: {
		addField() {
			this.edit_fields.push({
				type: 'InputText',
				data: {
					size: 50
				},
				label: 'Dynamically added field',
				value: new Date(),
			});
		}
	},
	components: {
		/* This can be further improved and made more dynamic with a variable in the import function,
		* Like we do with views. However, selecting the entire primevue component directory had some error side-effects from unexpected files in there.
		* */
		Button: defineAsyncComponent(() => import(/* webpackChunkName: "dynamic-editview-primevue-button" */'primevue/button') ),
		InputText: defineAsyncComponent(() => import(/* webpackChunkName: "dynamic-editview-primevue-inputtext" */'primevue/inputtext') ),
		Calendar: defineAsyncComponent(() => import(/* webpackChunkName: "dynamic-editview-primevue-calendar" */'primevue/calendar') ),
		Dropdown: defineAsyncComponent(() => import(/* webpackChunkName: "dynamic-editview-primevue-dropdown" */'primevue/dropdown') ),
		Radiobutton: defineAsyncComponent(() => import(/* webpackChunkName: "dynamic-editview-primevue-radiobutton" */'primevue/radiobutton') ),
	}
}