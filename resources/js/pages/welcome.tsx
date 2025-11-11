import { Head, Link } from '@inertiajs/react';

export default function Welcome({ tables = [] }: { tables?: any[] }) {
    return (
        <>
            <Head title='Table Availability' />
            <div className='min-h-screen bg-slate-50 p-8'>
                <div className='bg-white rounded-lg shadow p-6 mb-6'>
                    <h1 className='text-3xl font-bold mb-4 text-gray-900'>Restaurant Tables</h1>
                    <p className='text-lg mb-2 text-gray-700'>Total tables: {tables?.length || 0}</p>
                    <Link href='/login' className='inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700'>
                        Staff Login
                    </Link>
                </div>
                
                <div className='bg-white rounded-lg shadow p-6'>
                    <h2 className='text-2xl font-bold mb-4 text-gray-900'>Table List</h2>
                    
                    {!tables || tables.length === 0 ? (
                        <div className='text-center py-12 text-red-500'>
                            <p>No tables found!</p>
                            <pre className='mt-4 text-left bg-gray-100 p-4 rounded'>
                                {JSON.stringify({ tables }, null, 2)}
                            </pre>
                        </div>
                    ) : (
                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4'>
                            {tables.map((table, index) => (
                                <div 
                                    key={table?.id || index} 
                                    className={`p-6 rounded-lg border-2 text-center ${
                                        table?.status === 'available' 
                                            ? 'bg-green-50 border-green-300' 
                                            : 'bg-gray-200 border-gray-400 opacity-60'
                                    }`}
                                >
                                    <div className='text-2xl font-bold mb-2 text-gray-900'>{table?.number || 'N/A'}</div>
                                    <div className='text-sm text-gray-700'>Capacity: {table?.capacity || 0}</div>
                                    <div className={`text-xs mt-2 font-semibold uppercase ${
                                        table?.status === 'available' 
                                            ? 'text-green-700' 
                                            : 'text-gray-600'
                                    }`}>
                                        {table?.status || 'unknown'}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}