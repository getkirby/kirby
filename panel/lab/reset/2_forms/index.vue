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
				<k-lab-example label="select">
					<select>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="select disabled">
					<select disabled>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=text]">
					<input type="text" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=text] disabled">
					<input type="text" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=checkbox]">
					<input type="checkbox" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=checkbox] disabled">
					<input type="checkbox" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=radio]">
					<input type="radio" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=radio] disabled">
					<input type="radio" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="textarea">
					<textarea>Textarea</textarea>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="textarea" disabled>
					<textarea disabled>Textarea</textarea>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=range]">
					<input type="range" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="input[type=range] disabled">
					<input type="range" disabled />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="buttons">
					<button>Button</button>
					<input type="submit" value="Submit" />
					<input type="reset" value="Reset" />
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="buttons disabled">
					<button disabled>Button</button>
					<input disabled type="submit" value="Submit" />
					<input disabled type="reset" value="Reset" />
				</k-lab-example>
			</div>
		</k-grid>
		<k-lab-example label="label">
			<div>
				<label for="input">Connected</label>
				<input type="text" id="input" />
			</div>
			<div>
				<label>Not connected</label>
			</div>
		</k-lab-example>
		<k-lab-example label="fieldset">
			<fieldset>
				<legend>Fieldset Legend</legend>
			</fieldset>
		</k-lab-example>
	</k-lab-examples>
</template>
