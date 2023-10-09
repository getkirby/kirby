<template>
	<k-lab-examples>
		<k-grid
			style="
				--columns: 2;
				gap: var(--spacing-12);
				margin-bottom: var(--spacing-12);
			"
		>
			<div>
				<k-lab-example label="select" :code="false">
					<select>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="select disabled" :code="false">
					<select disabled>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=text]" :code="false">
					<input type="text" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=text] disabled" :code="false">
					<input type="text" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=checkbox]" :code="false">
					<input type="checkbox" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=checkbox] disabled" :code="false">
					<input type="checkbox" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=radio]" :code="false">
					<input type="radio" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=radio] disabled" :code="false">
					<input type="radio" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="textarea" :code="false">
					<textarea>Textarea</textarea>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="textarea disabled" :code="false">
					<textarea disabled>Textarea</textarea>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=range]" :code="false">
					<input type="range" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=range] disabled" :code="false">
					<input type="range" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="buttons" :code="false">
					<button>Button</button>
					<input type="submit" value="Submit" />
					<input type="reset" value="Reset" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="buttons disabled" :code="false">
					<button disabled>Button</button>
					<input disabled type="submit" value="Submit" />
					<input disabled type="reset" value="Reset" />
				</k-lab-example>
			</div>
		</k-grid>
		<k-lab-example label="label" :code="false">
			<div>
				<label for="input">Connected</label>
				<input id="input" type="text" />
			</div>
			<div>
				<label>Not connected</label>
			</div>
		</k-lab-example>
		<k-lab-example label="fieldset" :code="false">
			<fieldset>
				<legend>Fieldset Legend</legend>
			</fieldset>
		</k-lab-example>
	</k-lab-examples>
</template>
