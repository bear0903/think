//<script type="text/script" language="javascript">
//		<!--
// Global variable 记录最后一笔选中的 row 的所在的 GridView ID + 所选中的 row index 
// 以 GridView ID 为下标, 如
//	GridView_LastSelectedRows['grid1'] = 3
// 记录 gridview id 原因是当一个页面有多个 GridView 时的情形
var GridView_LastSelectedRows = new Array();
// 
/**
 * 设定选中的 row 的 style 为选中 style, 并记下当前的所点选的 row 的相关信息
 * 1.点选的 row 所在的 gridview 的 id
 * 2.所选的 row 的 row index (start with 0)
 * @param gridview_id string GridView (table)的 ID
 * @param rowObj object Mouse Click 的 Row
 * @param selected_style string 选中时的 style class name
 * @param normal_style string 正常的 style class name
 * @return void no return values
 * @access public
 * @author Dennis
 */
function GridView_SetRowSelected(gridview_id, rowObj, selected_style,
		normal_style) {
	//var gridview = document.getElementById(gridview_id);
	// 如果有选中的 row 清除其 style
	if (typeof(GridView_LastSelectedRows[gridview_id])!= 'undefined') {
		//alert(document.getElementById(gridview_id).rows.length)
		document.getElementById(gridview_id).rows[GridView_LastSelectedRows[gridview_id]].className = normal_style;
	}// end if
	rowObj.className = selected_style;
	GridView_LastSelectedRows[gridview_id] = rowObj.rowIndex;
}// end  GridView_SetRowSelected()
//	//-->
//	</script>