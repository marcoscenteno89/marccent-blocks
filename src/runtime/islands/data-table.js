// A marccent/data-table block: the theme's DataTable, from the columns and rows the server read from its CSV.
import { DataTable } from 'marccent-theme/components/ui/data-table';

export default function DataTableIsland( { columns = [], rows = [], label, pageSize = 10, search = true } ) {
	return <DataTable columns={ columns } rows={ rows } label={ label } pageSize={ pageSize } search={ search } />;
}
