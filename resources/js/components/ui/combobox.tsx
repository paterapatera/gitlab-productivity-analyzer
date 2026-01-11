import * as React from "react"
import { ChevronDownIcon, SearchIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Input } from "@/components/ui/input"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"
import { CommitProject } from "@/types/commit"

interface ComboboxProps {
  projects: CommitProject[]
  value?: string
  onValueChange: (value: string) => void
  placeholder?: string
  "aria-invalid"?: boolean
  required?: boolean
  id?: string
  className?: string
}

export function Combobox({
  projects,
  value,
  onValueChange,
  placeholder = "プロジェクトを選択してください",
  "aria-invalid": ariaInvalid,
  required,
  id,
  className,
}: ComboboxProps) {
  const [open, setOpen] = React.useState(false)
  const [searchValue, setSearchValue] = React.useState("")

  const selectedProject = React.useMemo(
    () => projects.find((p) => p.id.toString() === value),
    [projects, value]
  )

  const filteredProjects = React.useMemo(() => {
    if (!searchValue) {
      return projects
    }
    const lowerSearchValue = searchValue.toLowerCase()
    return projects.filter((project) =>
      project.name_with_namespace.toLowerCase().includes(lowerSearchValue)
    )
  }, [projects, searchValue])

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <button
          type="button"
          role="combobox"
          aria-expanded={open}
          aria-invalid={ariaInvalid}
          aria-required={required}
          id={id}
          data-slot="combobox-trigger"
          className={cn(
            "border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 dark:hover:bg-input/50 flex w-full items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-2 text-sm whitespace-nowrap shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 h-9 *:data-[slot=combobox-value]:line-clamp-1 *:data-[slot=combobox-value]:flex *:data-[slot=combobox-value]:items-center *:data-[slot=combobox-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
            className
          )}
        >
          <span data-slot="combobox-value">
            {selectedProject?.name_with_namespace || placeholder}
          </span>
          <ChevronDownIcon className="size-4 opacity-50" />
        </button>
      </PopoverTrigger>
      <PopoverContent
        className="w-[var(--radix-popover-trigger-width)] p-0"
        align="start"
      >
        <div className="p-2">
          <div className="relative">
            <SearchIcon className="absolute left-2 top-1/2 size-4 -translate-y-1/2 text-muted-foreground pointer-events-none" />
            <Input
              type="text"
              value={searchValue}
              onChange={(e) => setSearchValue(e.target.value)}
              placeholder="プロジェクトを検索..."
              className="pl-8"
              data-slot="combobox-input"
            />
          </div>
        </div>
        <div className="max-h-[300px] overflow-y-auto">
          {filteredProjects.length === 0 ? (
            <div className="p-4 text-center text-sm text-muted-foreground" data-slot="combobox-empty">
              該当するプロジェクトが見つかりません
            </div>
          ) : (
            <div className="p-1" data-slot="combobox-list">
              {filteredProjects.map((project) => (
                <div
                  key={project.id}
                  onClick={() => {
                    onValueChange(project.id.toString())
                    setOpen(false)
                    setSearchValue("")
                  }}
                  className={cn(
                    "focus:bg-accent focus:text-accent-foreground relative flex w-full cursor-pointer items-center gap-2 rounded-sm py-1.5 pr-8 pl-2 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 hover:bg-accent hover:text-accent-foreground",
                    value === project.id.toString() && "bg-accent text-accent-foreground"
                  )}
                  data-slot="combobox-item"
                >
                  {project.name_with_namespace}
                </div>
              ))}
            </div>
          )}
        </div>
      </PopoverContent>
    </Popover>
  )
}
