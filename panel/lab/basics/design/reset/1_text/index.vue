<template>
	<k-lab-examples>
		<k-lab-example label="headlines" :code="false">
			<h1>Headline 1</h1>
			<h2>Headline 2</h2>
			<h3>Headline 3</h3>
			<h4>Headline 4</h4>
			<h5>Headline 5</h5>
			<h6>Headline 6</h6>
		</k-lab-example>
		<k-lab-example label="bold" :code="false">
			<b>Bold</b>
			<strong>Strong</strong>
		</k-lab-example>
		<k-lab-example label="italic" :code="false">
			<i>Italic</i>
			<em>Italic</em>
		</k-lab-example>
		<k-lab-example label="lists" :code="false">
			<ul>
				<li>Unordered list item</li>
				<li>Unordered list item</li>
			</ul>
			<ol>
				<li>Ordered list item</li>
				<li>Ordered list item</li>
			</ol>
		</k-lab-example>
		<k-lab-example label="details" :code="false">
			<details>
				<summary>Summary</summary>
				Details
			</details>
		</k-lab-example>
		<k-lab-example label="table" :code="false">
			<table>
				<thead>
					<tr>
						<th>TH 1</th>
						<th>TH 2</th>
						<th>TH 2</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>TD 1</td>
						<td>TD 2</td>
						<td>TD 2</td>
					</tr>
				</tbody>
			</table>
		</k-lab-example>
		<k-lab-example label="hr" :code="false">
			<hr />
		</k-lab-example>
	</k-lab-examples>
</template>
