import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import interpret from "./dayjs-interpret.js";
import iso from "./dayjs-iso.js";
import merge from "./dayjs-merge.js";
import pattern from "./dayjs-pattern.js";
import round from "./dayjs-round.js";
import validate from "./dayjs-validate.js";

dayjs.extend(customParseFormat);
dayjs.extend(interpret);
dayjs.extend(iso);
dayjs.extend(merge);
dayjs.extend(pattern);
dayjs.extend(round);
dayjs.extend(validate);

export default dayjs;
