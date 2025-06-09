export default function CreateCategorySection() {
    return (
        <a href="/categories/create" className="w-full flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-lg my-8">
            <h2 className="text-lg font-semibold">Create new Category</h2>
            <p className="mt-4 text-sm">
                You can add a new category by clicking the button below
            </p>
        </a>
    );
}
