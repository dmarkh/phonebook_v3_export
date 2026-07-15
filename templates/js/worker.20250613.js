
importScripts("./polyfill.20250613.js");
importScripts("./exceljs.min.20250613.js");

self.addEventListener('message', async (e) => {
                try {
					const workbook = new ExcelJS.Workbook();
					await workbook.xlsx.load( e.data.file, {
					  ignoreNodes: [
					    'dataValidations' // ignores the workbook's Data Validations
					  ],
					});
					const worksheet = workbook.worksheets[0]
					jsondata = [];
					worksheet.eachRow( (row, rowNumber) => {
						rowdata = [];
						row.eachCell( (cell, colNumber) => {
							rowdata.push( cell.value );
						});
						jsondata.push( rowdata );
					})
                    postMessage({ jsondata });
                } catch(e) {
                    postMessage({ jsondata: String(e.message || e).bold() });
                }
}, false );
