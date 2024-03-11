<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-picklist-input
					name="picklist"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-picklist-input
					:autofocus="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-picklist-input
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Options with info">
				<k-picklist-input
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-picklist-input
					ref="input"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-picklist-input
					:disabled="true"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Multiple: false">
				<k-picklist-input :multiple="false" :options="options" value="c" />
			</k-lab-example>

			<k-lab-example label="Create">
				<k-picklist-input :create="true" :options="options" />
			</k-lab-example>

			<k-lab-example label="Min: 2">
				<k-picklist-input :create="true" :options="options" :min="2" />
			</k-lab-example>

			<k-lab-example label="Max: 2">
				<k-picklist-input :create="true" :options="options" :max="2" />
			</k-lab-example>

			<k-lab-example label="Search: display limited">
				<k-picklist-input :options="optionsMany" :search="{ display: 10 }" />
			</k-lab-example>

			<k-lab-example label="Search: false">
				<k-picklist-input :options="options" :search="false" />
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			value: null
		};
	},
	computed: {
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		},
		optionsWithInfo() {
			return [
				{ text: "Option A", value: "a", info: "This is some info text" },
				{ text: "Option B", value: "b", info: "This is some info text" },
				{ text: "Option C", value: "c", info: "This is some info text" }
			];
		},
		optionsMany() {
			return [
				{ text: "Afghanistan", value: "Afghanistan" },
				{ text: "Albania", value: "Albania" },
				{ text: "Algeria", value: "Algeria" },
				{ text: "Andorra", value: "Andorra" },
				{ text: "Angola", value: "Angola" },
				{ text: "Antigua & Deps", value: "Antigua & Deps" },
				{ text: "Argentina", value: "Argentina" },
				{ text: "Armenia", value: "Armenia" },
				{ text: "Australia", value: "Australia" },
				{ text: "Austria", value: "Austria" },
				{ text: "Azerbaijan", value: "Azerbaijan" },
				{ text: "Bahamas", value: "Bahamas" },
				{ text: "Bahrain", value: "Bahrain" },
				{ text: "Bangladesh", value: "Bangladesh" },
				{ text: "Barbados", value: "Barbados" },
				{ text: "Belarus", value: "Belarus" },
				{ text: "Belgium", value: "Belgium" },
				{ text: "Belize", value: "Belize" },
				{ text: "Benin", value: "Benin" },
				{ text: "Bhutan", value: "Bhutan" },
				{ text: "Bolivia", value: "Bolivia" },
				{ text: "Bosnia Herzegovina", value: "Bosnia Herzegovina" },
				{ text: "Botswana", value: "Botswana" },
				{ text: "Brazil", value: "Brazil" },
				{ text: "Brunei", value: "Brunei" },
				{ text: "Bulgaria", value: "Bulgaria" },
				{ text: "Burkina", value: "Burkina" },
				{ text: "Burundi", value: "Burundi" },
				{ text: "Cambodia", value: "Cambodia" },
				{ text: "Cameroon", value: "Cameroon" },
				{ text: "Canada", value: "Canada" },
				{ text: "Cape Verde", value: "Cape Verde" },
				{ text: "Central African Rep", value: "Central African Rep" },
				{ text: "Chad", value: "Chad" },
				{ text: "Chile", value: "Chile" },
				{ text: "China", value: "China" },
				{ text: "Colombia", value: "Colombia" },
				{ text: "Comoros", value: "Comoros" },
				{ text: "Congo", value: "Congo" },
				{ text: "Congo {Democratic Rep}", value: "Congo {Democratic Rep}" },
				{ text: "Costa Rica", value: "Costa Rica" },
				{ text: "Croatia", value: "Croatia" },
				{ text: "Cuba", value: "Cuba" },
				{ text: "Cyprus", value: "Cyprus" },
				{ text: "Czech Republic", value: "Czech Republic" },
				{ text: "Denmark", value: "Denmark" },
				{ text: "Djibouti", value: "Djibouti" },
				{ text: "Dominica", value: "Dominica" },
				{ text: "Dominican Republic", value: "Dominican Republic" },
				{ text: "East Timor", value: "East Timor" },
				{ text: "Ecuador", value: "Ecuador" },
				{ text: "Egypt", value: "Egypt" },
				{ text: "El Salvador", value: "El Salvador" },
				{ text: "Equatorial Guinea", value: "Equatorial Guinea" },
				{ text: "Eritrea", value: "Eritrea" },
				{ text: "Estonia", value: "Estonia" },
				{ text: "Ethiopia", value: "Ethiopia" },
				{ text: "Fiji", value: "Fiji" },
				{ text: "Finland", value: "Finland" },
				{ text: "France", value: "France" },
				{ text: "Gabon", value: "Gabon" },
				{ text: "Gambia", value: "Gambia" },
				{ text: "Georgia", value: "Georgia" },
				{ text: "Germany", value: "Germany" },
				{ text: "Ghana", value: "Ghana" },
				{ text: "Greece", value: "Greece" },
				{ text: "Grenada", value: "Grenada" },
				{ text: "Guatemala", value: "Guatemala" },
				{ text: "Guinea", value: "Guinea" },
				{ text: "Guinea-Bissau", value: "Guinea-Bissau" },
				{ text: "Guyana", value: "Guyana" },
				{ text: "Haiti", value: "Haiti" },
				{ text: "Honduras", value: "Honduras" },
				{ text: "Hungary", value: "Hungary" },
				{ text: "Iceland", value: "Iceland" },
				{ text: "India", value: "India" },
				{ text: "Indonesia", value: "Indonesia" },
				{ text: "Iran", value: "Iran" },
				{ text: "Iraq", value: "Iraq" },
				{ text: "Ireland {Republic}", value: "Ireland {Republic}" },
				{ text: "Israel", value: "Israel" },
				{ text: "Italy", value: "Italy" },
				{ text: "Ivory Coast", value: "Ivory Coast" },
				{ text: "Jamaica", value: "Jamaica" },
				{ text: "Japan", value: "Japan" },
				{ text: "Jordan", value: "Jordan" },
				{ text: "Kazakhstan", value: "Kazakhstan" },
				{ text: "Kenya", value: "Kenya" },
				{ text: "Kiribati", value: "Kiribati" },
				{ text: "Korea North", value: "Korea North" },
				{ text: "Korea South", value: "Korea South" },
				{ text: "Kosovo", value: "Kosovo" },
				{ text: "Kuwait", value: "Kuwait" },
				{ text: "Kyrgyzstan", value: "Kyrgyzstan" },
				{ text: "Laos", value: "Laos" },
				{ text: "Latvia", value: "Latvia" },
				{ text: "Lebanon", value: "Lebanon" },
				{ text: "Lesotho", value: "Lesotho" },
				{ text: "Liberia", value: "Liberia" },
				{ text: "Libya", value: "Libya" },
				{ text: "Liechtenstein", value: "Liechtenstein" },
				{ text: "Lithuania", value: "Lithuania" },
				{ text: "Luxembourg", value: "Luxembourg" },
				{ text: "Macedonia", value: "Macedonia" },
				{ text: "Madagascar", value: "Madagascar" },
				{ text: "Malawi", value: "Malawi" },
				{ text: "Malaysia", value: "Malaysia" },
				{ text: "Maldives", value: "Maldives" },
				{ text: "Mali", value: "Mali" },
				{ text: "Malta", value: "Malta" },
				{ text: "Marshall Islands", value: "Marshall Islands" },
				{ text: "Mauritania", value: "Mauritania" },
				{ text: "Mauritius", value: "Mauritius" },
				{ text: "Mexico", value: "Mexico" },
				{ text: "Micronesia", value: "Micronesia" },
				{ text: "Moldova", value: "Moldova" },
				{ text: "Monaco", value: "Monaco" },
				{ text: "Mongolia", value: "Mongolia" },
				{ text: "Montenegro", value: "Montenegro" },
				{ text: "Morocco", value: "Morocco" },
				{ text: "Mozambique", value: "Mozambique" },
				{ text: "Myanmar, {Burma}", value: "Myanmar, {Burma}" },
				{ text: "Namibia", value: "Namibia" },
				{ text: "Nauru", value: "Nauru" },
				{ text: "Nepal", value: "Nepal" },
				{ text: "Netherlands", value: "Netherlands" },
				{ text: "New Zealand", value: "New Zealand" },
				{ text: "Nicaragua", value: "Nicaragua" },
				{ text: "Niger", value: "Niger" },
				{ text: "Nigeria", value: "Nigeria" },
				{ text: "Norway", value: "Norway" },
				{ text: "Oman", value: "Oman" },
				{ text: "Pakistan", value: "Pakistan" },
				{ text: "Palau", value: "Palau" },
				{ text: "Panama", value: "Panama" },
				{ text: "Papua New Guinea", value: "Papua New Guinea" },
				{ text: "Paraguay", value: "Paraguay" },
				{ text: "Peru", value: "Peru" },
				{ text: "Philippines", value: "Philippines" },
				{ text: "Poland", value: "Poland" },
				{ text: "Portugal", value: "Portugal" },
				{ text: "Qatar", value: "Qatar" },
				{ text: "Romania", value: "Romania" },
				{ text: "Russian Federation", value: "Russian Federation" },
				{ text: "Rwanda", value: "Rwanda" },
				{ text: "St Kitts & Nevis", value: "St Kitts & Nevis" },
				{ text: "St Lucia", value: "St Lucia" },
				{
					text: "Saint Vincent & the Grenadines",
					value: "Saint Vincent & the Grenadines"
				},
				{ text: "Samoa", value: "Samoa" },
				{ text: "San Marino", value: "San Marino" },
				{ text: "Sao Tome & Principe", value: "Sao Tome & Principe" },
				{ text: "Saudi Arabia", value: "Saudi Arabia" },
				{ text: "Senegal", value: "Senegal" },
				{ text: "Serbia", value: "Serbia" },
				{ text: "Seychelles", value: "Seychelles" },
				{ text: "Sierra Leone", value: "Sierra Leone" },
				{ text: "Singapore", value: "Singapore" },
				{ text: "Slovakia", value: "Slovakia" },
				{ text: "Slovenia", value: "Slovenia" },
				{ text: "Solomon Islands", value: "Solomon Islands" },
				{ text: "Somalia", value: "Somalia" },
				{ text: "South Africa", value: "South Africa" },
				{ text: "South Sudan", value: "South Sudan" },
				{ text: "Spain", value: "Spain" },
				{ text: "Sri Lanka", value: "Sri Lanka" },
				{ text: "Sudan", value: "Sudan" },
				{ text: "Suriname", value: "Suriname" },
				{ text: "Swaziland", value: "Swaziland" },
				{ text: "Sweden", value: "Sweden" },
				{ text: "Switzerland", value: "Switzerland" },
				{ text: "Syria", value: "Syria" },
				{ text: "Taiwan", value: "Taiwan" },
				{ text: "Tajikistan", value: "Tajikistan" },
				{ text: "Tanzania", value: "Tanzania" },
				{ text: "Thailand", value: "Thailand" },
				{ text: "Togo", value: "Togo" },
				{ text: "Tonga", value: "Tonga" },
				{ text: "Trinidad & Tobago", value: "Trinidad & Tobago" },
				{ text: "Tunisia", value: "Tunisia" },
				{ text: "Turkey", value: "Turkey" },
				{ text: "Turkmenistan", value: "Turkmenistan" },
				{ text: "Tuvalu", value: "Tuvalu" },
				{ text: "Uganda", value: "Uganda" },
				{ text: "Ukraine", value: "Ukraine" },
				{ text: "United Arab Emirates", value: "United Arab Emirates" },
				{ text: "United Kingdom", value: "United Kingdom" },
				{ text: "United States", value: "United States" },
				{ text: "Uruguay", value: "Uruguay" },
				{ text: "Uzbekistan", value: "Uzbekistan" },
				{ text: "Vanuatu", value: "Vanuatu" },
				{ text: "Vatican City", value: "Vatican City" },
				{ text: "Venezuela", value: "Venezuela" },
				{ text: "Vietnam", value: "Vietnam" },
				{ text: "Yemen", value: "Yemen" },
				{ text: "Zambia", value: "Zambia" },
				{ text: "Zimbabwe", value: "Zimbabwe" }
			];
		}
	}
};
</script>
