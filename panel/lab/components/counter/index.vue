<template>
	<k-lab-examples>
		<k-lab-example label="simple">
			<k-counter :count="1" />
		</k-lab-example>
		<k-lab-example label="required:true / invalid">
			<k-counter :count="0" :required="true" />
		</k-lab-example>
		<k-lab-example label="required:true / valid">
			<k-counter :count="1" :required="true" />
		</k-lab-example>
		<k-lab-example label="min:5 / invalid">
			<k-counter :count="1" :min="5" />
		</k-lab-example>
		<k-lab-example label="min:5 / valid">
			<k-counter :count="5" :min="5" />
		</k-lab-example>
		<k-lab-example label="max:5 / invalid">
			<k-counter :count="10" :max="5" />
		</k-lab-example>
		<k-lab-example label="max:5 / valid">
			<k-counter :count="5" :max="5" />
		</k-lab-example>
		<k-lab-example label="min:5 & max:10 / invalid">
			<k-counter :count="1" :min="5" :max="10" />
		</k-lab-example>
		<k-lab-example label="min:5 & max:10 / valid">
			<k-counter :count="7" :min="5" :max="10" />
		</k-lab-example>
	</k-lab-examples>
</template>
