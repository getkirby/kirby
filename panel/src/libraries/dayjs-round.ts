/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, ManipulateType, PluginFunc, UnitTypeLong } from "dayjs";

declare module "dayjs" {
	interface Dayjs {
		/**
		 * Rounds the datetime to the nearest step of a unit,
		 * e.g. to the nearest 15 minutes
		 *
		 * All sub-units of the step unit are cleared, down to the
		 * milliseconds. Their rounding cascades stepwise, from the
		 * smallest sub-unit upwards, which is not always the
		 * absolutely nearest step: e.g. 13:45 rounded to a 4 hour
		 * step first carries the minutes over to 14:00 and then
		 * rounds up to 16:00, instead of down to the nearer 12:00.
		 * This is intended, as it keeps the behavior predictable
		 * while typing.
		 *
		 * @param unit unit to round to; `day` is read as `date`
		 * @param size how many of the unit make up one step; has to
		 *             divide the unit evenly (e.g. 15 of 60 minutes),
		 *             calendar units only support 1
		 * @throws if the unit or the step size is not supported
		 */
		round(unit: UnitTypeLong, size: number): Dayjs;
	}
}

/**
 * Units a datetime can be rounded to, from the least to the
 * most significant one. The order is what makes a unit's
 * sub-units the entries before it.
 *
 * `day` is absent on purpose: it is normalized to `date`
 * before the list is ever consulted.
 */
const units: Exclude<UnitTypeLong, "day">[] = [
	"millisecond",
	"second",
	"minute",
	"hour",
	"date",
	"month",
	"year"
];

/**
 * Ceilings of the units whose step sizes have to divide
 * evenly; date, month and year have no divisible ceiling
 */
const ceilings: Partial<Record<UnitTypeLong, number>> = {
	millisecond: 1000,
	second: 60,
	minute: 60,
	hour: 24
};

/**
 * Whether the step size is supported for the step unit:
 * for time units the size has to divide the unit's ceiling
 * evenly (e.g. 15 of 60 minutes, 4 of 24 hours), calendar
 * units only support single steps
 */
function isValidSize(unit: UnitTypeLong, size: number): boolean {
	if (Number.isInteger(size) === false || size < 1) {
		return false;
	}

	const ceiling = ceilings[unit];

	if (ceiling === undefined) {
		return size === 1;
	}

	return ceiling % size === 0;
}

const plugin: PluginFunc = (_option, Dayjs) => {
	Dayjs.prototype.round = function (
		unit: UnitTypeLong = "date",
		size: number = 1
	): Dayjs {
		if (unit === "day") {
			unit = "date";
		}

		// Validate step unit
		if (units.includes(unit) === false) {
			throw new Error("Invalid rounding unit");
		}

		// Validate step size
		if (isValidSize(unit, size) === false) {
			throw new Error("Invalid rounding size for " + unit);
		}

		const index = units.indexOf(unit);
		const subsubunits = units.slice(0, index);
		const subunit = subsubunits.pop();

		// set all subunits (except the direct predecessor) to its start
		let dt = subsubunits.reduce<Dayjs>(
			(dt, subsubunit) => dt.startOf(subsubunit),
			this
		);

		// if a direct predecessor subunit exists,
		// check if rounding leads to a carry over
		if (subunit !== undefined) {
			// ceiling of the direct predecessor subunit; the days
			// of the month are the only dynamic one
			const ceiling =
				subunit === "month"
					? 12
					: subunit === "date"
						? dt.daysInMonth()
						: ceilings[subunit];

			// check if the subunit rounds up to its ceiling,
			// if so carry it over into the step unit
			if (ceiling !== undefined && dt.get(subunit) * 2 >= ceiling) {
				dt = dt.add(1, (unit === "date" ? "day" : unit) as ManipulateType);
			}

			// set subunit to its start
			dt = dt.startOf(unit);
		}

		// round the main step unit
		return dt.set(unit, Math.round(dt.get(unit) / size) * size);
	};
};

export default plugin;
