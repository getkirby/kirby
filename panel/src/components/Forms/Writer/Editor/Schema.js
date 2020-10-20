/** Schema */
import CodeSchema from "../Schemas/Code.js";
import DefaultSchema from "../Schemas/Default.js";

export default function (marks, code) {
    return code ? CodeSchema() : DefaultSchema(marks);
};
