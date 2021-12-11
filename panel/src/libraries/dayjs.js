import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import merge from "./dayjs-merge.js";
import pattern from "./dayjs-pattern.js";
import round from "./dayjs-round.js";
import units from "./dayjs-units.js";
import utc from "dayjs/plugin/utc";

dayjs.extend(customParseFormat);
dayjs.extend(merge);
dayjs.extend(pattern);
dayjs.extend(round);
dayjs.extend(units);
dayjs.extend(utc);

export default dayjs;
