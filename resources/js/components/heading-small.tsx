export default function HeadingSmall({ title, smallTitle, description }: { title: string; smallTitle?:string, description?: string }) {
    return (
        <header>
            <h3 className="mb-0.5 text-base font-medium">
                {title} {smallTitle && <span className="text-xs text-muted-foreground">({smallTitle})</span>}
            </h3>
            {description && <p className="text-muted-foreground text-sm">{description}</p>}
        </header>
    );
}
