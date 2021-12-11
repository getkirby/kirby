import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import round from "./dayjs-round.js";
import utc from "dayjs/plugin/utc";

dayjs.extend(customParseFormat);
dayjs.extend(round);
dayjs.extend(utc);

export default dayjs;
